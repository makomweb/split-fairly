<?php

declare(strict_types=1);

namespace App\Instrumentation\Null;

use App\Instrumentation\MetricsInterface;

final readonly class Metrics implements MetricsInterface
{
    public function record(string $name, float|int $value, string $unit): void
    {
    }
}
