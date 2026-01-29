<?php

declare(strict_types=1);

namespace App\SplitFairly;

use App\SplitFairly\DTO\Expense;

final readonly class ExpenseTracker
{
    public function __construct(
        private readonly EventStoreInterface $eventStore,
        private readonly NormalizerInterface $normalizer,
        private readonly CurrentUserInterface $currentUser,
    ) {
    }

    public function track(Expense $expense): void
    {
        $event = new Event(
            createdBy: $this->currentUser->getUuid(),
            subjectType: array_last(explode('\\', get_class($expense))),
            subjectId: $expense->getId()->toRfc4122(),
            eventType: 'tracked',
            payload: $this->normalizer->toArray($expense, ['id'])
        );

        $this->eventStore->persist(event: $event, dontCommit: false);
    }
}
