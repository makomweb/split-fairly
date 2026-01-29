<?php

declare(strict_types=1);

namespace App\SplitFairly;

use App\SplitFairly\DTO\Expense;
use App\SplitFairly\DTO\Expenses;

final readonly class Calculator
{
    public function __construct(
        private readonly EventStoreInterface $eventStore,
        private readonly DenormalizerInterface $denormalizer,
    ) {
    }

    /**
     * @return Expenses[]
     */
    public function calculate(): array
    {
        $userIds = $this->eventStore->getUserIds();

        $events = $this->eventStore->getEvents(
            new QueryOptions(
                createdBy: $userIds,
                subjectTypes: ['Expense'],
                eventTypes: ['tracked']
            )
        );

        return array_reduce(
            $events,
            $this->reduce(...),
            array_map(
                static fn (string $userId): Expenses => Expenses::initial($userId),
                $userIds
            )
        );
    }

    /**
     * @param Expenses[] $expenses
     *
     * @return Expenses[]
     */
    private function reduce(array $expenses, Event $event): array
    {
        $found = array_find(
            $expenses,
            static fn (Expenses $expense): bool => $expense->userId === $event->createdBy
        );

        assert($found instanceof Expenses);

        $expense = $this->denormalizer->fromArray(
            $event->payload,
            Expense::class
        );

        assert($expense instanceof Expense);

        $found->add($expense);

        return $expenses;
    }
}
