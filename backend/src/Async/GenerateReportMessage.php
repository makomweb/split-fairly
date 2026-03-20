<?php

declare(strict_types=1);

namespace App\Async;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(transport: 'async')]
final class GenerateReportMessage
{
    public function __construct(
        public readonly int $reportId,
        public readonly string $compensationId,
    ) {
    }
}
