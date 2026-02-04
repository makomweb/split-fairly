<?php

declare(strict_types=1);

namespace App\Instrumentation;

interface SpanInterface
{
    public function open(): void;

    public function recordException(string $context, \Throwable $ex): void;

    public function close(): void;
}
