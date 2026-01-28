<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Async\Message;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsEventListener]
final readonly class LoginCounter
{
    public function __construct(
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(ResponseEvent $event): void
    {
        // TODO:

        $this->bus->dispatch(Message::create('🛎️ new request'));
    }
}
