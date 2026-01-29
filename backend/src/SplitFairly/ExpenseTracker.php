<?php

declare(strict_types=1);

namespace App\SplitFairly;

use App\SplitFairly\DTO\Expense;

final readonly class ExpenseTracker
{
    public function __construct(
        private readonly EventStoreInterface $eventStore,
        private readonly NormalizerInterface $normalizer,
    ) {
    }

    public function track(Expense $expense): void
    {
        $event = new Event(
            subjectType: array_last(explode('\\', get_class($expense))),
            subjectId: $expense->getId()->toRfc4122(),
            eventType: 'tracked',
            payload: $this->normalizer->toArray($expense, ['id'])
        );

        $this->eventStore->persist(event: $event, dontCommit: false);
    }
}
