<?php

declare(strict_types=1);

namespace App\Tests\Unit\Instrumentation\PsrLog;

use App\Instrumentation\PsrLog\Span;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class SpanTest extends TestCase
{
    private LoggerInterface&MockObject $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function test_open_logs_debug_message(): void
    {
        $this->logger->expects($this->once())
            ->method('info')
            ->with('Entering testMethod');

        $span = new Span('testMethod', $this->logger);
        $span->open();
    }

    public function test_close_without_exception_logs_exiting(): void
    {
        $this->logger->expects($this->atLeast(2))
            ->method('info');

        $span = new Span('testMethod', $this->logger);
        $span->open();
        $span->close();
    }

    public function test_close_with_recorded_exception(): void
    {
        $exception = new \Exception('Test error');

        $this->logger->expects($this->atLeast(2))
            ->method('info');

        $span = new Span('testMethod', $this->logger);
        $span->open();
        $span->recordException('test', $exception);
        $span->close();
    }

    public function test_record_exception_does_not_log(): void
    {
        $this->logger->expects($this->once())
            ->method('info');

        $span = new Span('testMethod', $this->logger);
        $span->open();
        $span->recordException('test', new \Exception('Test error'));
    }

    public function test_span_lifecycle(): void
    {
        $this->logger->expects($this->exactly(2))
            ->method('info');

        $span = new Span('method1', $this->logger);
        $span->open();
        $span->close();
    }
}
