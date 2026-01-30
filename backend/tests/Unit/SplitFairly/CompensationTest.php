<?php

declare(strict_types=1);

namespace App\Tests\Unit\SplitFairly;

use App\SplitFairly\Compensation;
use App\SplitFairly\Expense;
use App\SplitFairly\Expenses;
use App\SplitFairly\Price;
use PHPUnit\Framework\TestCase;

final class CompensationTest extends TestCase
{
    public function test_compensation_when_first_user_spent_more(): void
    {
        $user1Id = 'user-1';
        $user1Email = 'user1@example.com';
        $user2Id = 'user-2';
        $user2Email = 'user2@example.com';

        $expenses1 = Expenses::initial($user1Id, $user1Email);
        $expenses1->add(new Expense(
            price: new Price(20.0, 'EUR'),
            what: 'Groceries',
            type: 'Food',
            location: 'Market'
        ));
        $expenses1->add(new Expense(
            price: new Price(30.0, 'EUR'),
            what: 'Dinner',
            type: 'Food',
            location: 'Restaurant'
        ));

        $expenses2 = Expenses::initial($user2Id, $user2Email);
        $expenses2->add(new Expense(
            price: new Price(25.0, 'EUR'),
            what: 'Lunch',
            type: 'Food',
            location: 'Cafe'
        ));

        $compensation = Compensation::calculate($expenses1, $expenses2);

        $this->assertSame($user2Email, $compensation->from);
        $this->assertSame($user1Email, $compensation->to);
        $this->assertSame(25.0, $compensation->settlement->value);
        $this->assertSame('EUR', $compensation->settlement->currency);
    }

    public function test_compensation_when_second_user_spent_more(): void
    {
        $user1Id = 'user-1';
        $user1Email = 'user1@example.com';
        $user2Id = 'user-2';
        $user2Email = 'user2@example.com';

        $expenses1 = Expenses::initial($user1Id, $user1Email);
        $expenses1->add(new Expense(
            price: new Price(15.0, 'EUR'),
            what: 'Groceries',
            type: 'Food',
            location: 'Market'
        ));

        $expenses2 = Expenses::initial($user2Id, $user2Email);
        $expenses2->add(new Expense(
            price: new Price(20.0, 'EUR'),
            what: 'Lunch',
            type: 'Food',
            location: 'Cafe'
        ));
        $expenses2->add(new Expense(
            price: new Price(30.0, 'EUR'),
            what: 'Dinner',
            type: 'Food',
            location: 'Restaurant'
        ));

        $compensation = Compensation::calculate($expenses1, $expenses2);

        $this->assertSame($user1Email, $compensation->from);
        $this->assertSame($user2Email, $compensation->to);
        $this->assertSame(35.0, $compensation->settlement->value);
        $this->assertSame('EUR', $compensation->settlement->currency);
    }

    public function test_compensation_with_equal_spending(): void
    {
        $user1Id = 'user-1';
        $user1Email = 'user1@example.com';
        $user2Id = 'user-2';
        $user2Email = 'user2@example.com';

        $expenses1 = Expenses::initial($user1Id, $user1Email);
        $expenses1->add(new Expense(
            price: new Price(25.0, 'EUR'),
            what: 'Groceries',
            type: 'Food',
            location: 'Market'
        ));

        $expenses2 = Expenses::initial($user2Id, $user2Email);
        $expenses2->add(new Expense(
            price: new Price(25.0, 'EUR'),
            what: 'Lunch',
            type: 'Food',
            location: 'Cafe'
        ));

        $compensation = Compensation::calculate($expenses1, $expenses2);

        $this->assertSame(0.0, $compensation->settlement->value);
        $this->assertSame('EUR', $compensation->settlement->currency);
    }

    public function test_compensation_with_zero_expenses_for_first_user(): void
    {
        $user1Id = 'user-1';
        $user1Email = 'user1@example.com';
        $user2Id = 'user-2';
        $user2Email = 'user2@example.com';

        $expenses1 = Expenses::initial($user1Id, $user1Email);

        $expenses2 = Expenses::initial($user2Id, $user2Email);
        $expenses2->add(new Expense(
            price: new Price(50.0, 'EUR'),
            what: 'Groceries',
            type: 'Food',
            location: 'Market'
        ));

        $compensation = Compensation::calculate($expenses1, $expenses2);

        $this->assertSame($user1Email, $compensation->from);
        $this->assertSame($user2Email, $compensation->to);
        $this->assertSame(50.0, $compensation->settlement->value);
        $this->assertSame('EUR', $compensation->settlement->currency);
    }

    public function test_compensation_with_zero_expenses_for_second_user(): void
    {
        $user1Id = 'user-1';
        $user1Email = 'user1@example.com';
        $user2Id = 'user-2';
        $user2Email = 'user2@example.com';

        $expenses1 = Expenses::initial($user1Id, $user1Email);
        $expenses1->add(new Expense(
            price: new Price(75.0, 'EUR'),
            what: 'Groceries',
            type: 'Food',
            location: 'Market'
        ));

        $expenses2 = Expenses::initial($user2Id, $user2Email);

        $compensation = Compensation::calculate($expenses1, $expenses2);

        $this->assertSame($user2Email, $compensation->from);
        $this->assertSame($user1Email, $compensation->to);
        $this->assertSame(75.0, $compensation->settlement->value);
        $this->assertSame('EUR', $compensation->settlement->currency);
    }

    public function test_compensation_with_zero_expenses_for_both_users(): void
    {
        $user1Id = 'user-1';
        $user1Email = 'user1@example.com';
        $user2Id = 'user-2';
        $user2Email = 'user2@example.com';

        $expenses1 = Expenses::initial($user1Id, $user1Email);
        $expenses2 = Expenses::initial($user2Id, $user2Email);

        $compensation = Compensation::calculate($expenses1, $expenses2);

        $this->assertSame(0.0, $compensation->settlement->value);
        $this->assertSame('EUR', $compensation->settlement->currency);
    }

    public function test_compensation_with_decimal_values(): void
    {
        $user1Id = 'user-1';
        $user1Email = 'user1@example.com';
        $user2Id = 'user-2';
        $user2Email = 'user2@example.com';

        $expenses1 = Expenses::initial($user1Id, $user1Email);
        $expenses1->add(new Expense(
            price: new Price(15.75, 'EUR'),
            what: 'Groceries',
            type: 'Food',
            location: 'Market'
        ));
        $expenses1->add(new Expense(
            price: new Price(12.50, 'EUR'),
            what: 'Coffee',
            type: 'Food',
            location: 'Cafe'
        ));

        $expenses2 = Expenses::initial($user2Id, $user2Email);
        $expenses2->add(new Expense(
            price: new Price(10.25, 'EUR'),
            what: 'Lunch',
            type: 'Food',
            location: 'Restaurant'
        ));

        $compensation = Compensation::calculate($expenses1, $expenses2);

        $this->assertSame($user2Email, $compensation->from);
        $this->assertSame($user1Email, $compensation->to);
        $this->assertSame(18.0, $compensation->settlement->value);
        $this->assertSame('EUR', $compensation->settlement->currency);
    }

    public function test_compensation_calculates_absolute_difference(): void
    {
        $user1Id = 'user-1';
        $user1Email = 'user1@example.com';
        $user2Id = 'user-2';
        $user2Email = 'user2@example.com';

        $expenses1 = Expenses::initial($user1Id, $user1Email);
        $expenses1->add(new Expense(
            price: new Price(100.0, 'EUR'),
            what: 'Rent split',
            type: 'Housing',
            location: 'Apartment'
        ));

        $expenses2 = Expenses::initial($user2Id, $user2Email);
        $expenses2->add(new Expense(
            price: new Price(25.0, 'EUR'),
            what: 'Utilities split',
            type: 'Utilities',
            location: 'Apartment'
        ));

        $compensation = Compensation::calculate($expenses1, $expenses2);

        // Difference is 75.0, and it should be positive (absolute value)
        $this->assertSame(75.0, $compensation->settlement->value);
        $this->assertGreaterThan(0, $compensation->settlement->value);
    }

    public function test_compensation_with_multiple_categories(): void
    {
        $user1Id = 'user-1';
        $user1Email = 'user1@example.com';
        $user2Id = 'user-2';
        $user2Email = 'user2@example.com';

        $expenses1 = Expenses::initial($user1Id, $user1Email);
        $expenses1->add(new Expense(
            price: new Price(30.0, 'EUR'),
            what: 'Groceries',
            type: 'Food',
            location: 'Market'
        ));
        $expenses1->add(new Expense(
            price: new Price(30.0, 'EUR'),
            what: 'Groceries',
            type: 'Food',
            location: 'Market'
        ));
        $expenses1->add(new Expense(
            price: new Price(20.0, 'EUR'),
            what: 'Gas',
            type: 'Transport',
            location: 'Gas Station'
        ));

        $expenses2 = Expenses::initial($user2Id, $user2Email);
        $expenses2->add(new Expense(
            price: new Price(40.0, 'EUR'),
            what: 'Lunch',
            type: 'Food',
            location: 'Restaurant'
        ));

        $compensation = Compensation::calculate($expenses1, $expenses2);

        // User 1 spent 80, User 2 spent 40, difference is 40
        $this->assertSame($user2Email, $compensation->from);
        $this->assertSame($user1Email, $compensation->to);
        $this->assertSame(40.0, $compensation->settlement->value);
    }
}
