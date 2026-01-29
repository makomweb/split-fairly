<?php

namespace App\Repository;

use App\Entity\Event as EventEntity;
use App\SplitFairly\CurrentUserInterface;
use App\SplitFairly\Event as DomainEvent;
use App\SplitFairly\EventStoreInterface;
use App\SplitFairly\QueryOptions as Options;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

final readonly class EventRepository implements EventStoreInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CurrentUserInterface $currentUser,
    ) {
    }

    public function persist(DomainEvent $event, bool $dontCommit = false): void
    {
        $entity = new EventEntity(
            $this->currentUser->getUuid(),
            $event->subjectType,
            $event->subjectId,
            $event->eventType,
            $event->payload,
            $event->createdAt
        );

        $this->entityManager->persist($entity);

        if (!$dontCommit) {
            $this->entityManager->flush();
        }
    }

    public function reset(): void
    {
        $repo = $this->entityManager->getRepository(EventEntity::class);

        $entities = $repo->findAll();
        foreach ($entities as $entity) {
            $this->entityManager->remove($entity);
        }

        $this->entityManager->flush();
    }

    /** @return DomainEvent[] */
    public function getEvents(Options $options = new Options()): array
    {
        $events = $this
            ->createQueryBuilder($options)
            ->getQuery()
            ->getResult();

        assert(is_array($events));

        return array_map(
            static function ($entity) {
                assert($entity instanceof EventEntity);

                return new DomainEvent(
                    $entity->getSubjectType(),
                    $entity->getSubjectId(),
                    $entity->getEventType(),
                    $entity->getPayload(),
                    $entity->getCreatedAt(),
                    $entity->getCreatedBy()
                );
            },
            $events
        );
    }

    private function createQueryBuilder(Options $options): QueryBuilder
    {
        $repository = $this->entityManager->getRepository(EventEntity::class);

        // TODO: put users into the options!
        // $builder = $repository
        //     ->createQueryBuilder('e')
        //     ->where('e.createdBy = :uuid')
        //     ->orderBy('e.createdAt', 'ASC')
        //     ->setParameter('uuid', $this->currentUser->getUuid());

        $builder = $repository
            ->createQueryBuilder('e')
            ->orderBy('e.createdAt', 'ASC');

        if (!empty($options->subjectTypes)) {
            $builder = $builder
                ->where('e.subjectType IN (:subjectTypes)')
                ->setParameter('subjectTypes', $options->subjectTypes);
        }

        if (empty($options->subjectTypes) && !empty($options->subjectIds)) {
            $builder = $builder
                ->where('e.subjectId IN (:subjectIds)')
                ->setParameter('subjectIds', $options->subjectIds);
        } elseif (!empty($options->subjectIds)) {
            $builder = $builder
                ->andWhere('e.subjectId IN (:subjectIds)')
                ->setParameter('subjectIds', $options->subjectIds);
        }

        if (empty($options->subjectTypes) && empty($options->subjectIds) && !empty($options->eventTypes)) {
            $builder = $builder
                ->where('e.eventType IN (:eventTypes)')
                ->setParameter('eventTypes', $options->eventTypes);
        } elseif (!empty($options->eventTypes)) {
            $builder = $builder
                ->andWhere('e.eventType IN (:eventTypes)')
                ->setParameter('eventTypes', $options->eventTypes);
        }

        return $builder;
    }
}
