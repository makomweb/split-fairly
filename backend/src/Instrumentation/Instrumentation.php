<?php

declare(strict_types=1);

namespace App\Instrumentation;

use App\Instrumentation\Null\Logging as NullLogging;
use App\Instrumentation\Null\Metrics as NullMetrics;
use App\Instrumentation\Null\Tracing as NullTracing;
use App\Instrumentation\PsrLog\Logging as PsrLogLogging;
use App\Instrumentation\PsrLog\Metrics as PsrLogMetrics;
use App\Instrumentation\PsrLog\Tracing as PsrLogTracing;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class Instrumentation
{
    public function __construct(
        #[Autowire('%app.telemetry%')]
        private string $telemetry,
        private LoggerInterface $logger,
    ) {
    }

    public function getLogging(): LoggingInterface
    {
        return match ($this->telemetry) {
            'PsrLog' => new PsrLogLogging($this->logger),
            'Null' => new NullLogging(),
            default => throw new \InvalidArgumentException(sprintf('"%s" not supported!', $this->telemetry)),
        };
    }

    public function getMetrics(): MetricsInterface
    {
        return match ($this->telemetry) {
            'PsrLog' => new PsrLogMetrics($this->logger),
            'Null' => new NullMetrics(),
            default => throw new \InvalidArgumentException(sprintf('"%s" not supported!', $this->telemetry)),
        };
    }

    public function getTracing(): TracingInterface
    {
        return match ($this->telemetry) {
            'PsrLog' => new PsrLogTracing($this->logger),
            'Null' => new NullTracing(),
            default => throw new \InvalidArgumentException(sprintf('"%s" not supported!', $this->telemetry)),
        };
    }
}
