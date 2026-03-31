<?php

declare(strict_types=1);

namespace App\Tests\Unit\Instrumentation\PsrLog;

use App\Instrumentation\PsrLog\Logging;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class LoggingTest extends TestCase
{
    private LoggerInterface&MockObject $logger;

    private Logging $logging;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->logging = new Logging($this->logger);
    }

    public function test_info_calls_logger_info(): void
    {
        $message = 'Test message';

        $this->logger->expects($this->once())
            ->method('info')
            ->with($message);

        $this->logging->info($message);
    }

    public function test_info_with_stringable(): void
    {
        $stringable = new class implements \Stringable {
            public function __toString(): string
            {
                return 'Stringable message';
            }
        };

        $this->logger->expects($this->once())
            ->method('info')
            ->with($stringable);

        $this->logging->info($stringable);
    }

    public function test_exception_logs_error_with_context(): void
    {
        $exception = new \Exception('Test error message');

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                'Test error message',
                $this->callback(fn(mixed $context) => is_array($context)
                    && isset($context['exception_type'])
                    && $context['exception_type'] === $exception::class
                    && isset($context['stack_trace'])
                    && $context['stack_trace'] === $exception->getTrace())
            );

        $this->logging->exception($exception);
    }

    public function test_exception_with_custom_exception(): void
    {
        $exception = new class extends \Exception {
            public function __construct()
            {
                parent::__construct('Custom exception');
            }
        };

        $this->logger->expects($this->once())
            ->method('error');

        $this->logging->exception($exception);
    }
}
