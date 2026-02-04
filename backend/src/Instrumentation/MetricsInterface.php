<?php

declare(strict_types=1);

namespace App\Instrumentation;

interface MetricsInterface
{
    public function record(string $name, float|int $value, string $unit): void;
}
