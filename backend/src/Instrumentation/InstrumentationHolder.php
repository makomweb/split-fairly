<?php

declare(strict_types=1);

namespace App\Instrumentation;

use Psr\Log\LoggerInterface;

final class InstrumentationHolder
{
    private static ?Instrumentation $instance = null;

    public static function initialize(string $telemetry, LoggerInterface $logger): void
    {
        assert(in_array($telemetry, ['PsrLog', 'Null'], strict: true), 'Either "PsrLog" or "Null" is allowed for parameter $telemetry!');

        if (is_null(self::$instance)) {
            self::$instance = new Instrumentation($telemetry, $logger);
        }
    }

    public static function getLogging(): LoggingInterface
    {
        assert(!is_null(self::$instance), 'Not yet initialized!');

        return self::$instance->getLogging();
    }

    public static function getMetrics(): MetricsInterface
    {
        assert(!is_null(self::$instance), 'Not yet initialized!');

        return self::$instance->getMetrics();
    }

    public static function getTracing(): TracingInterface
    {
        assert(!is_null(self::$instance), 'Not yet initialized!');

        return self::$instance->getTracing();
    }
}
