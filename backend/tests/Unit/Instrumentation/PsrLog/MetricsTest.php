<?php

declare(strict_types=1);

namespace App\Tests\Unit\Instrumentation\PsrLog;

use App\Instrumentation\PsrLog\Metrics;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class MetricsTest extends TestCase
{
    private LoggerInterface&MockObject $logger;
    private Metrics $metrics;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->metrics = new Metrics($this->logger);
    }

    public function test_record_logs_notice_with_integer_value(): void
    {
        $this->logger->expects($this->once())
            ->method('info')
            ->with('📈 Metric recorded: requests = 100 count');

        $this->metrics->record('requests', 100, 'count');
    }

    public function test_record_logs_notice_with_float_value(): void
    {
        $this->logger->expects($this->once())
            ->method('info')
            ->with('📈 Metric recorded: duration = 0.5 seconds');

        $this->metrics->record('duration', 0.5, 'seconds');
    }

    public function test_record_with_special_characters_in_name(): void
    {
        $this->logger->expects($this->once())
            ->method('info')
            ->with('📈 Metric recorded: http.request.duration = 42 ms');

        $this->metrics->record('http.request.duration', 42, 'ms');
    }

    public function test_record_multiple_metrics(): void
    {
        $this->logger->expects($this->exactly(2))
            ->method('info');

        $this->metrics->record('metric1', 10, 'unit1');
        $this->metrics->record('metric2', 20, 'unit2');
    }
}
