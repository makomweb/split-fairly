<?php

declare(strict_types=1);

namespace App\Instrumentation\PsrLog;

use App\Instrumentation\MetricsInterface;
use Psr\Log\LoggerInterface;

final readonly class Metrics implements MetricsInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function record(string $name, float|int $value, string $unit): void
    {
        $this->logger->info("📈 Metric recorded: {$name} = {$value} {$unit}");
    }
}
