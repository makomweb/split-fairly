<?php

declare(strict_types=1);

namespace App\Instrumentation\PsrLog;

use App\Instrumentation\Tracer;
use App\Instrumentation\TracingInterface;
use Psr\Log\LoggerInterface;

final readonly class Tracing implements TracingInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    /**
     * @param non-empty-string     $methodName
     * @param non-empty-string     $file
     * @param array<string,string> $traceContext
     */
    public function createTracer(string $methodName, string $file, array $traceContext = []): Tracer
    {
        return new Tracer(new Span($methodName, $this->logger));
    }
}
