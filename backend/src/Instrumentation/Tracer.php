<?php

declare(strict_types=1);

namespace App\Instrumentation;

/**
 * When an instance of this class goes out of scope
 * it calls SpanInterface::close() to indicate the span is closed.
 */
final readonly class Tracer
{
    public function __construct(private SpanInterface $span)
    {
        $span->open();
    }

    public function recordException(string $context, \Throwable $ex): void
    {
        $this->span->recordException($context, $ex);
    }

    public function __destruct()
    {
        $this->span->close();
    }
}
