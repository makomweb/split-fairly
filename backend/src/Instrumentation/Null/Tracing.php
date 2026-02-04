<?php

declare(strict_types=1);

namespace App\Instrumentation\Null;

use App\Instrumentation\Tracer;
use App\Instrumentation\TracingInterface;

final readonly class Tracing implements TracingInterface
{
    /**
     * @param non-empty-string     $methodName
     * @param non-empty-string     $file
     * @param array<string,string> $traceContext
     */
    public function createTracer(string $methodName, string $file, array $traceContext = []): Tracer
    {
        return new Tracer(new Span());
    }
}
