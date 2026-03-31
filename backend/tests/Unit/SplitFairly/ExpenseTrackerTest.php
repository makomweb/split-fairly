<?php

declare(strict_types=1);

namespace App\Tests\Unit\SplitFairly;

use App\SplitFairly\CurrentUserInterface;
use App\SplitFairly\Event;
use App\SplitFairly\EventStoreInterface;
use App\SplitFairly\Expense;
use App\SplitFairly\ExpenseTracker;
use App\SplitFairly\NormalizerInterface;
use App\SplitFairly\Price;
use PHPUnit\Framework\TestCase;

final class ExpenseTrackerTest extends TestCase
{
    public function test_tracks_expense_and_persists_event(): void
    {
        $price = new Price(value: 10.50, currency: 'EUR');
        $expense = new Expense(price: $price, what: 'Coffee', type: 'Groceries', location: 'Starbucks');

        $currentUser = $this->createMock(CurrentUserInterface::class);
        $currentUser->expects($this->any())->method('getUuid')->willReturn('user-123');

        $normalizedPayload = ['price' => (string) $price, 'what' => 'Coffee', 'type' => 'Groceries', 'location' => 'Starbucks'];
        $normalizer = $this->createMock(NormalizerInterface::class);
        $normalizer->expects($this->once())->method('toArray')->with($expense, ['id'])->willReturn($normalizedPayload);

        $eventStore = $this->createMock(EventStoreInterface::class);
        $eventStore->expects($this->once())->method('persist')->with(
            $this->callback(function (Event $event) use ($expense, $normalizedPayload): bool {
                return 'user-123' === $event->createdBy
                    && 'Expense' === $event->subjectType
                    && $event->subjectId === $expense->getId()->toRfc4122()
                    && 'tracked' === $event->eventType
                    && $event->payload === $normalizedPayload;
            }),
            false
        );

        $tracker = new ExpenseTracker($eventStore, $normalizer, $currentUser);

        $tracker->track($expense);
    }
}
