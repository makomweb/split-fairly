<?php

declare(strict_types=1);

namespace App\Tests\Unit\Instrumentation;

use App\Instrumentation\Initializer;
use App\Instrumentation\InstrumentationHolder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class InitializerTest extends TestCase
{
    private LoggerInterface&MockObject $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);

        // Reset the Holder singleton
        $reflection = new \ReflectionClass(InstrumentationHolder::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, null);
    }

    public function test_initializer_initializes_provider_on_invoke(): void
    {
        $initializer = new Initializer('PsrLog', $this->logger);

        $request = Request::create('/');
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $initializer($event);

        // Verify initialization worked by calling getLogging
        $logging = InstrumentationHolder::getLogging();
        self::assertInstanceOf(\App\Instrumentation\LoggingInterface::class, $logging);
    }

    public function test_initializer_with_null_telemetry(): void
    {
        $initializer = new Initializer('Null', $this->logger);

        $request = Request::create('/');
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $initializer($event);

        $logging = InstrumentationHolder::getLogging();
        self::assertInstanceOf(\App\Instrumentation\LoggingInterface::class, $logging);
    }
}
