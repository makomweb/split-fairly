<?php

declare(strict_types=1);

namespace App\Instrumentation\PsrLog;

use App\Instrumentation\LoggingInterface;
use Psr\Log\LoggerInterface;

final readonly class Logging implements LoggingInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function info(string|\Stringable $message): void
    {
        $this->logger->info($message);
    }

    public function debug(string|\Stringable $message): void
    {
        $this->logger->debug($message);
    }

    public function exception(\Throwable $ex): void
    {
        $this->logger
            ->error($ex->getMessage(), [
                'exception_type' => get_class($ex),
                'stack_trace' => $ex->getTrace(),
            ]);
    }
}
