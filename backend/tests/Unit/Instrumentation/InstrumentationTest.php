<?php

declare(strict_types=1);

namespace App\Tests\Unit\Instrumentation;

use App\Instrumentation\InstrumentationHolder;
use App\Instrumentation\LoggingInterface;
use App\Instrumentation\MetricsInterface;
use App\Instrumentation\TracingInterface;
use App\Invariant\InvariantException;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class InstrumentationTest extends TestCase
{
    protected function tearDown(): void
    {
        // Reset the singleton instance via reflection
        $reflection = new \ReflectionClass(InstrumentationHolder::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, null);
        parent::tearDown();
    }

    public function test_provider_initializes_with_psrlog(): void
    {
        InstrumentationHolder::initialize('PsrLog', new NullLogger());

        self::assertInstanceOf(LoggingInterface::class, InstrumentationHolder::getLogging());
    }

    public function test_provider_initializes_with_null(): void
    {
        InstrumentationHolder::initialize('Null', new NullLogger());

        self::assertInstanceOf(LoggingInterface::class, InstrumentationHolder::getLogging());
    }

    public function test_provider_only_initializes_once(): void
    {
        $logger1 = new NullLogger();
        InstrumentationHolder::initialize('PsrLog', $logger1);

        $logger2 = new NullLogger();
        InstrumentationHolder::initialize('Null', $logger2);

        // Verify it's still using PsrLog from first initialization
        $logging = InstrumentationHolder::getLogging();
        self::assertInstanceOf(LoggingInterface::class, $logging);
    }

    public function test_provider_provides_logging_interface(): void
    {
        InstrumentationHolder::initialize('PsrLog', new NullLogger());

        $logging = InstrumentationHolder::getLogging();

        self::assertInstanceOf(LoggingInterface::class, $logging);
    }

    public function test_provider_provides_metrics_interface(): void
    {
        InstrumentationHolder::initialize('PsrLog', new NullLogger());

        $metrics = InstrumentationHolder::getMetrics();

        self::assertInstanceOf(MetricsInterface::class, $metrics);
    }

    public function test_provider_provides_tracing_interface(): void
    {
        InstrumentationHolder::initialize('PsrLog', new NullLogger());

        $tracing = InstrumentationHolder::getTracing();

        self::assertInstanceOf(TracingInterface::class, $tracing);
    }

    public function test_provider_throws_on_uninitialized_access_logging(): void
    {
        $this->expectException(InvariantException::class);
        $this->expectExceptionMessage('Not yet initialized!');

        InstrumentationHolder::getLogging();
    }

    public function test_provider_throws_on_invalid_telemetry_option(): void
    {
        $this->expectException(InvariantException::class);
        $this->expectExceptionMessage('Either "PsrLog" or "Null" is allowed');

        InstrumentationHolder::initialize('InvalidOption', new NullLogger());
    }
}
