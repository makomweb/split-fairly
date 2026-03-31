<?php

declare(strict_types=1);

namespace App\Instrumentation;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;

#[AsEventListener()]
final readonly class Initializer
{
    public function __construct(
        #[Autowire('%app.telemetry%')]
        private string $telemetry,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        InstrumentationHolder::initialize($this->telemetry, $this->logger);
    }
}
