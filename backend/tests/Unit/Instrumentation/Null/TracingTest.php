<?php

declare(strict_types=1);

namespace App\Tests\Unit\Instrumentation\Null;

use App\Instrumentation\Null\Tracing;
use App\Instrumentation\Tracer;
use PHPUnit\Framework\TestCase;

final class TracingTest extends TestCase
{
    private Tracing $tracing;

    protected function setUp(): void
    {
        $this->tracing = new Tracing();
    }

    public function test_create_tracer_returns_tracer_instance(): void
    {
        $tracer = $this->tracing->createTracer('testMethod', __FILE__);

        self::assertInstanceOf(Tracer::class, $tracer);
    }

    public function test_create_tracer_with_context(): void
    {
        $context = ['request_id' => 'abc123', 'user_id' => 'user-1'];
        $tracer = $this->tracing->createTracer('testMethod', __FILE__, $context);

        self::assertInstanceOf(Tracer::class, $tracer);
    }

    public function test_create_tracer_multiple_calls(): void
    {
        $tracer1 = $this->tracing->createTracer('method1', __FILE__);
        $tracer2 = $this->tracing->createTracer('method2', __FILE__);

        self::assertInstanceOf(Tracer::class, $tracer1);
        self::assertInstanceOf(Tracer::class, $tracer2);
        self::assertNotSame($tracer1, $tracer2);
    }
}
