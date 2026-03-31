<?php

declare(strict_types=1);

namespace App\Async;

use App\Entity\Report;
use App\Instrumentation\Instrumentation;
use App\Invariant\Ensure;
use App\Repository\ReportRepository;
use App\SplitFairly\Calculator;
use App\SplitFairly\Compensation;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Twig\Environment;

#[AsMessageHandler]
final class GenerateReportHandler
{
    public function __construct(
        private readonly Instrumentation $instrumentation,
        private readonly EntityManagerInterface $entityManager,
        private readonly ReportRepository $reportRepository,
        private readonly Calculator $calculator,
        private readonly Environment $twig,
        #[Autowire('%kernel.project_dir%/var/reports')] private readonly string $reportsDir,
    ) {
    }

    public function __invoke(GenerateReportMessage $message): void
    {
        $report = null;
        $timer = Stopwatch::start();

        try {
            // Clear any stale entity state
            $this->entityManager->clear();

            $report = $this->reportRepository->find($message->id);
            if (!$report instanceof Report) {
                throw new \RuntimeException(sprintf('Report with ID %s not found', $message->id));
            }

            $report->setStatus(Report::STATUS_GENERATING);
            $this->entityManager->persist($report);
            $this->entityManager->flush();

            $expenses = $this->calculator->calculate();

            if (2 !== count($expenses)) {
                throw new \RuntimeException('Expected exactly 2 users in calculation');
            }

            $compensation = Compensation::calculate($expenses[0], $expenses[1]);

            $html = $this->twig->render('report/calculation.html.twig', [
                'expenses' => $expenses,
                'compensation' => $compensation,
            ]);

            $dompdf = new Dompdf($this->createDompdfOptions());
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $pdfContent = $dompdf->output();
            $fileName = sprintf('report-%s.pdf', $report->getId());
            $filePath = $this->reportsDir.DIRECTORY_SEPARATOR.$fileName;

            Ensure::that(false !== file_put_contents($filePath, $pdfContent));

            $report->setFilePath($filePath);
            $report->setStatus(Report::STATUS_COMPLETED);
            $report->setCompletedAt(new \DateTimeImmutable());
            $this->entityManager->flush();

            $this->instrumentation->getLogging()->info(sprintf(
                'Report generated successfully: %s (%.0fms)',
                $fileName,
                $timer->getMillisecondsElapsed()
            ));
        } catch (\Exception $e) {
            $this->instrumentation->getLogging()->info(sprintf(
                'Failed to generate report: %s',
                $e->getMessage()
            ));

            if ($report instanceof Report) {
                $report->setStatus(Report::STATUS_FAILED);
                $report->setErrorMessage($e->getMessage());
                $report->setCompletedAt(new \DateTimeImmutable());
                $this->entityManager->flush();
            }

            throw $e;
        }
    }

    private function createDompdfOptions(): Options
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultMediaType', 'print');
        $options->set('isFontSubsettingEnabled', true);
        $options->set('isPhpEnabled', false);

        return $options;
    }
}
