<?php

declare(strict_types=1);

namespace App\Instrumentation\Null;

use App\Instrumentation\SpanInterface;

final readonly class Span implements SpanInterface
{
    public function open(): void
    {
    }

    public function recordException(string $context, \Throwable $ex): void
    {
    }

    public function close(): void
    {
    }
}
