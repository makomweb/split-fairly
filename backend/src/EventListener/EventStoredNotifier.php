<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Async\Message;
use App\Entity\Event as EventEntity;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Listen to entity persist events and notify the message bus
 * to, e.g. let the worker refresh the read model(s).
 *
 * @see https://symfony.com/doc/current/doctrine/events.html#doctrine-entity-listeners
 */
#[AsEntityListener(event: Events::postPersist, method: 'onPostPersist', entity: EventEntity::class)]
final readonly class EventStoredNotifier
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public function onPostPersist(EventEntity $entity, PostPersistEventArgs $args): void
    {
        $message = Message::create('🛎️ new event persisted');

        $this->bus->dispatch($message);
    }
}
