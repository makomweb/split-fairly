<?php

declare(strict_types=1);

namespace App\Tests\Unit\Instrumentation\Null;

use App\Instrumentation\Null\Metrics;
use PHPUnit\Framework\TestCase;

final class MetricsTest extends TestCase
{
    private Metrics $metrics;

    protected function setUp(): void
    {
        $this->metrics = new Metrics();
    }

    public function test_record_with_integer_value(): void
    {
        $this->metrics->record('requests', 100, 'count');
        $this->expectNotToPerformAssertions();
    }

    public function test_record_with_float_value(): void
    {
        $this->metrics->record('duration', 0.5, 'seconds');
        $this->expectNotToPerformAssertions();
    }

    public function test_record_with_any_metric(): void
    {
        $this->metrics->record('any_metric', 42, 'unit');
        $this->expectNotToPerformAssertions();
    }
}
