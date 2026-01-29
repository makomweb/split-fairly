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
        // Get all expense events that were tracked (from all users)
        $events = $this->eventStore->getEvents(
            new QueryOptions(
                subjectTypes: ['Expense'],
                eventTypes: ['tracked']
            )
        );

        // Group expenses by user (createdBy)
        $expensesByUser = [];

        foreach ($events as $event) {
            // Denormalize the expense from the event payload
            $expense = $this->denormalizer->fromArray(
                $event->payload,
                Expense::class
            );

            $userId = $event->createdBy;

            // Initialize user's expenses collection if not exists
            if (!isset($expensesByUser[$userId])) {
                $expensesByUser[$userId] = [];
            }

            $expensesByUser[$userId][] = $expense;
        }

        // Convert to Expenses objects
        $result = [];
        foreach ($expensesByUser as $userId => $expenses) {
            $result[] = new Expenses($userId, $expenses);
        }

        return $result;
    }
}
