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
     * @param array<Expenses> $expenses
     *
     * @return array<Expenses>
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

        /** @var array<Expenses> $initialExpenses */
        $initialExpenses = array_map(
            static fn (string $userId): Expenses => Expenses::initial($userId),
            $userIds
        );

        return array_reduce(
            $events,
            $this->reduce(...),
            $initialExpenses
        );

        // // Get all expense events that were tracked (from all users)
        // $events = $this->eventStore->getEvents(
        //     new QueryOptions(
        //         subjectTypes: ['Expense'],
        //         eventTypes: ['tracked']
        //     )
        // );

        // // Group expenses by user (createdBy)
        // $expensesByUser = [];

        // foreach ($events as $event) {
        //     // Denormalize the expense from the event payload
        //     $expense = $this->denormalizer->fromArray(
        //         $event->payload,
        //         Expense::class
        //     );

        //     $userId = $event->createdBy;

        //     // Initialize user's expenses collection if not exists
        //     if (!isset($expensesByUser[$userId])) {
        //         $expensesByUser[$userId] = [];
        //     }

        //     $expensesByUser[$userId][] = $expense;
        // }

        // // Convert to Expenses objects
        // $result = [];
        // foreach ($expensesByUser as $userId => $expenses) {
        //     $result[] = new Expenses($userId, $expenses);
        // }

        // return $result;
    }
}
