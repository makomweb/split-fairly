<?php

declare(strict_types=1);

namespace App\Instrumentation\PsrLog;

use App\Instrumentation\SpanInterface;
use Psr\Log\LoggerInterface;

final class Span implements SpanInterface
{
    /**
     * @var array<string, \Throwable>
     */
    private array $recordedExceptions = [];

    public function __construct(
        private string $methodName,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function open(): void
    {
        $this->logger->info('Entering '.$this->methodName);
    }

    public function recordException(string $context, \Throwable $ex): void
    {
        $this->recordedExceptions[$context] = $ex;
    }

    public function close(): void
    {
        if (!empty($this->recordedExceptions)) {
            foreach ($this->recordedExceptions as $context => $ex) {
                $this->logger->error(
                    sprintf('💥 %s: %s', $context, $ex->getMessage()),
                    [
                        'exception_type' => get_class($ex),
                        'stack_trace' => $ex->getTrace(),
                    ]
                );
            }
            $this->logger->info('Leaving '.$this->methodName);
        } else {
            $this->logger->info('Exiting '.$this->methodName);
        }
    }
}
