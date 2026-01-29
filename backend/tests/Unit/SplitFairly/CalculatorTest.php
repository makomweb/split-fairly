<?php

declare(strict_types=1);

namespace App\Tests\Unit\SplitFairly;

use App\SplitFairly\Calculator;
use App\SplitFairly\DenormalizerInterface;
use App\SplitFairly\DTO\Expense;
use App\SplitFairly\DTO\Expenses;
use App\SplitFairly\DTO\Price;
use App\SplitFairly\Event;
use App\SplitFairly\EventStoreInterface;
use App\SplitFairly\QueryOptions;
use PHPUnit\Framework\TestCase;

final class CalculatorTest extends TestCase
{
    public function testCalculateReturnsEmptyArrayWhenNoEvents(): void
    {
        $eventStore = $this->createMock(EventStoreInterface::class);
        $eventStore->method('getEvents')->willReturn([]);

        $denormalizer = $this->createMock(DenormalizerInterface::class);

        $calculator = new Calculator($eventStore, $denormalizer);

        $result = $calculator->calculate();

        $this->assertSame([], $result);
    }

    public function testCalculateGroupsExpensesByMultipleUsers(): void
    {
        $user1 = 'user-123';
        $user2 = 'user-456';
        
        $price = new Price(value: 10.50, currency: 'EUR');
        $expense1 = new Expense(price: $price, what: 'Coffee', location: 'Starbucks');
        $expense2 = new Expense(price: $price, what: 'Lunch', location: 'Restaurant');
        $expense3 = new Expense(price: $price, what: 'Dinner', location: 'Pizzeria');

        $event1 = new Event(
            subjectType: 'Expense',
            subjectId: 'exp-1',
            eventType: 'tracked',
            payload: ['price' => ['value' => 10.50, 'currency' => 'EUR'], 'what' => 'Coffee', 'location' => 'Starbucks'],
            createdAt: new \DateTimeImmutable(),
            createdBy: $user1
        );
        $event2 = new Event(
            subjectType: 'Expense',
            subjectId: 'exp-2',
            eventType: 'tracked',
            payload: ['price' => ['value' => 10.50, 'currency' => 'EUR'], 'what' => 'Lunch', 'location' => 'Restaurant'],
            createdAt: new \DateTimeImmutable(),
            createdBy: $user1
        );
        $event3 = new Event(
            subjectType: 'Expense',
            subjectId: 'exp-3',
            eventType: 'tracked',
            payload: ['price' => ['value' => 10.50, 'currency' => 'EUR'], 'what' => 'Dinner', 'location' => 'Pizzeria'],
            createdAt: new \DateTimeImmutable(),
            createdBy: $user2
        );

        $eventStore = $this->createMock(EventStoreInterface::class);
        $eventStore
            ->expects($this->once())
            ->method('getEvents')
            ->with($this->callback(function (QueryOptions $options) {
                return $options->subjectTypes === ['Expense'] 
                    && $options->eventTypes === ['tracked'];
            }))
            ->willReturn([$event1, $event2, $event3]);

        $denormalizer = $this->createMock(DenormalizerInterface::class);
        $denormalizer
            ->method('fromArray')
            ->willReturnOnConsecutiveCalls($expense1, $expense2, $expense3);

        $calculator = new Calculator($eventStore, $denormalizer);

        $result = $calculator->calculate();

        $this->assertCount(2, $result);
        
        // Find user1's expenses
        $user1Expenses = array_filter($result, fn($e) => $e->userId === $user1);
        $user1Expenses = array_values($user1Expenses)[0];
        $this->assertInstanceOf(Expenses::class, $user1Expenses);
        $this->assertSame($user1, $user1Expenses->userId);
        $this->assertCount(2, $user1Expenses->expenses);
        
        // Find user2's expenses
        $user2Expenses = array_filter($result, fn($e) => $e->userId === $user2);
        $user2Expenses = array_values($user2Expenses)[0];
        $this->assertInstanceOf(Expenses::class, $user2Expenses);
        $this->assertSame($user2, $user2Expenses->userId);
        $this->assertCount(1, $user2Expenses->expenses);
    }

    public function testCalculateFiltersOnlyTrackedExpenseEvents(): void
    {
        $eventStore = $this->createMock(EventStoreInterface::class);
        $eventStore
            ->expects($this->once())
            ->method('getEvents')
            ->with($this->callback(function (QueryOptions $options) {
                return $options->subjectTypes === ['Expense'] 
                    && $options->eventTypes === ['tracked'];
            }))
            ->willReturn([]);

        $denormalizer = $this->createMock(DenormalizerInterface::class);

        $calculator = new Calculator($eventStore, $denormalizer);

        $calculator->calculate();
    }
}
