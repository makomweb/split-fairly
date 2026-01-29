<?php

declare(strict_types=1);

namespace App\Tests\Unit\SplitFairly\DTO;

use App\SplitFairly\DTO\Expense;
use App\SplitFairly\DTO\Price;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class ExpenseTest extends TestCase
{
    public function test_can_create_expense(): void
    {
        $price = new Price(value: 10.50, currency: 'EUR');
        $expense = new Expense(
            price: $price,
            what: 'Coffee',
            location: 'Starbucks'
        );

        $this->assertSame($price, $expense->price);
        $this->assertSame('Coffee', $expense->what);
        $this->assertSame('Starbucks', $expense->location);
    }

    public function test_generates_consistent_id(): void
    {
        $price = new Price(value: 10.50, currency: 'EUR');
        $expense1 = new Expense(price: $price, what: 'Coffee', location: 'Starbucks');
        $expense2 = new Expense(price: $price, what: 'Coffee', location: 'Starbucks');

        $this->assertEquals($expense1->getId(), $expense2->getId());
    }

    public function test_generates_different_id_for_different_expenses(): void
    {
        $price = new Price(value: 10.50, currency: 'EUR');
        $expense1 = new Expense(price: $price, what: 'Coffee', location: 'Starbucks');
        $expense2 = new Expense(price: $price, what: 'Lunch', location: 'Restaurant');

        $this->assertNotEquals($expense1->getId(), $expense2->getId());
    }

    public function test_id_is_uuid_v5(): void
    {
        $price = new Price(value: 10.50, currency: 'EUR');
        $expense = new Expense(price: $price, what: 'Coffee', location: 'Starbucks');

        $id = $expense->getId();

        $this->assertInstanceOf(Uuid::class, $id);
        $this->assertTrue(Uuid::isValid($id->toRfc4122()));
    }

    public function test_rejects_empty_what(): void
    {
        $this->expectException(\App\Invariant\InvariantException::class);

        $price = new Price(value: 10.50, currency: 'EUR');
        new Expense(price: $price, what: '', location: 'Starbucks');
    }

    public function test_rejects_empty_location(): void
    {
        $this->expectException(\App\Invariant\InvariantException::class);

        $price = new Price(value: 10.50, currency: 'EUR');
        new Expense(price: $price, what: 'Coffee', location: '');
    }
}
