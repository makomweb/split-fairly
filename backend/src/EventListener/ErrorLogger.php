<?php

declare(strict_types=1);

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

#[AsEventListener(event: ExceptionEvent::class)]
final readonly class ErrorLogger
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        $exception = $event->getThrowable();
        $this->logger->error(
            sprintf('💥 Exception during request: %s %s', $request->getMethod(), $request->getBaseUrl()),
            ['exception' => $exception]
        );
    }
}
