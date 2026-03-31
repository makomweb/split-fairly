<?php

declare(strict_types=1);

namespace App\SplitFairly;

final readonly class ExpenseTracker
{
    public function __construct(
        private EventStoreInterface $eventStore,
        private NormalizerInterface $normalizer,
        private CurrentUserInterface $currentUser,
    ) {
    }

    public function track(Expense $expense): void
    {
        $event = new Event(
            createdBy: $this->currentUser->getUuid(),
            subjectType: array_last(explode('\\', $expense::class)),
            subjectId: $expense->getId()->toRfc4122(),
            eventType: 'tracked',
            payload: $this->normalizer->toArray($expense, ignoreFields: ['id'])
        );

        $this->eventStore->persist(event: $event, dontCommit: false);
    }
}
