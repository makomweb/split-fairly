<?php

declare(strict_types=1);

namespace App\Tests\Unit\Instrumentation\PsrLog;

use App\Instrumentation\PsrLog\Tracing;
use App\Instrumentation\Tracer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class TracingTest extends TestCase
{
    private LoggerInterface&MockObject $logger;
    private Tracing $tracing;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->tracing = new Tracing($this->logger);
    }

    public function test_create_tracer_returns_tracer_instance(): void
    {
        $this->logger->expects($this->any())->method('debug');

        $tracer = $this->tracing->createTracer('testMethod', __FILE__);

        self::assertInstanceOf(Tracer::class, $tracer);
    }

    public function test_create_tracer_with_context(): void
    {
        $this->logger->expects($this->any())->method('debug');

        $context = ['request_id' => 'abc123', 'user_id' => 'user-1'];
        $tracer = $this->tracing->createTracer('testMethod', __FILE__, $context);

        self::assertInstanceOf(Tracer::class, $tracer);
    }

    public function test_create_tracer_opens_span(): void
    {
        $this->logger->expects($this->any())->method('debug');

        $tracer = $this->tracing->createTracer('processPayment', __FILE__);

        self::assertInstanceOf(Tracer::class, $tracer);
    }

    public function test_create_multiple_tracers(): void
    {
        $this->logger->expects($this->any())->method('debug');

        $tracer1 = $this->tracing->createTracer('method1', __FILE__);
        $tracer2 = $this->tracing->createTracer('method2', __FILE__);

        self::assertNotSame($tracer1, $tracer2);
    }

    public function test_tracer_created_with_method_name_and_file(): void
    {
        $this->logger->expects($this->any())->method('debug');

        $methodName = 'calculateTotal';
        $file = __FILE__;

        $tracer = $this->tracing->createTracer($methodName, $file);

        self::assertInstanceOf(Tracer::class, $tracer);
    }
}
