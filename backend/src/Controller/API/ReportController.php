<?php

namespace App\Controller\API;

use App\Invariant\Ensure;
use App\SplitFairly\Calculator;
use App\SplitFairly\Compensation;
use Dompdf\Dompdf;
use Dompdf\Options;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api.')]
final class ReportController extends AbstractController
{
    public function __construct(
        private readonly Calculator $calculator,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/report/calculation', name: 'report.calculation', methods: ['GET'])]
    public function calculationReport(): Response
    {
        $expenses = $this->calculator->calculate();

        Ensure::that(2 === count($expenses));

        $compensation = Compensation::calculate($expenses[0], $expenses[1]);

        $html = $this->renderView('report/calculation.html.twig', [
            'expenses' => $expenses,
            'compensation' => $compensation,
        ]);

        $dompdf = new Dompdf($this->createDompdfOptions());
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdfContent = $dompdf->output();
        $fileName = sprintf('split-fairly-report-%s.pdf', (new \DateTimeImmutable())->format('Y-m-d'));

        $this->logger->info('Generated calculation report PDF', ['file' => $fileName]);

        return new Response(
            $pdfContent,
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf('attachment; filename="%s"', $fileName),
            ]
        );
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
