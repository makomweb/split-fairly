<?php

declare(strict_types=1);

namespace App\Async;

use App\Entity\Report;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(transport: 'async')]
final class GenerateReportMessage
{
    private function __construct(
        public readonly string $id,
    ) {
    }

    public static function fromReport(Report $report): self
    {
        return new self($report->getId());
    }
}
