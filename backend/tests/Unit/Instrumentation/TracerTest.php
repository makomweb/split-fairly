<?php

declare(strict_types=1);

namespace App\Tests\Unit\Instrumentation;

use App\Instrumentation\SpanInterface;
use App\Instrumentation\Tracer;
use PHPUnit\Framework\TestCase;

final class TracerTest extends TestCase
{
    public function test_tracer_opens_span_on_construction(): void
    {
        $span = $this->createMock(SpanInterface::class);
        $span->expects($this->once())->method('open');

        new Tracer($span);
    }

    public function test_tracer_closes_span_on_destruction(): void
    {
        $span = $this->createMock(SpanInterface::class);
        $span->expects($this->once())->method('open');
        $span->expects($this->once())->method('close');

        $tracer = new Tracer($span);
        unset($tracer);
    }

    public function test_tracer_records_exception(): void
    {
        $span = $this->createMock(SpanInterface::class);
        $span->expects($this->once())->method('open');
        $span->expects($this->once())->method('close');

        $exception = new \Exception('Test exception');
        $span->expects($this->once())->method('recordException')->with('test', $exception);

        $tracer = new Tracer($span);
        $tracer->recordException('test', $exception);
        unset($tracer);
    }
}
