<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Async\Message;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

#[AsEventListener]
final readonly class LoginCounter
{
    public function __construct(
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(LoginSuccessEvent $event): void
    {
        $userId = $event->getUser()->getUserIdentifier();

        $this->bus->dispatch(Message::create("🔐 Successful login: {$userId}"));
    }
}
