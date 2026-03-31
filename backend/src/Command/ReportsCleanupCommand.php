<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:reports:cleanup',
    description: 'Remove old report files and database entries',
)]
final class ReportsCleanupCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ReportRepository $reportRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'days',
            'd',
            InputOption::VALUE_OPTIONAL,
            'Remove reports older than N days',
            7
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $daysOption = $input->getOption('days');
        assert(is_numeric($daysOption), 'Days option is not a number!');
        $days = (int) $daysOption;
        $cutoffDate = new \DateTimeImmutable(sprintf('-%d days', $days));

        $oldReports = $this->reportRepository->findOlderThan($cutoffDate);

        if ([] === $oldReports) {
            $output->writeln('No old reports found.');

            return Command::SUCCESS;
        }

        $deletedCount = 0;
        foreach ($oldReports as $report) {
            $filePath = $report->getFilePath();
            if (null !== $filePath && file_exists($filePath)) {
                unlink($filePath);
            }

            $this->entityManager->remove($report);
            ++$deletedCount;
        }

        $this->entityManager->flush();

        $output->writeln(sprintf('Deleted %d report(s).', $deletedCount));

        return Command::SUCCESS;
    }
}
