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
            type: 'Groceries',
            location: 'Market'
        ));
        $expenses1->add(new Expense(
            price: new Price(30.0, 'EUR'),
            what: 'Dinner',
            type: 'Groceries',
            location: 'Restaurant'
        ));

        $expenses2 = Expenses::initial($user2Id, $user2Email);
        $expenses2->add(new Expense(
            price: new Price(25.0, 'EUR'),
            what: 'Lunch',
            type: 'Non-Food',
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
            type: 'Groceries',
            location: 'Market'
        ));

        $expenses2 = Expenses::initial($user2Id, $user2Email);
        $expenses2->add(new Expense(
            price: new Price(20.0, 'EUR'),
            what: 'Lunch',
            type: 'Non-Food',
            location: 'Cafe'
        ));
        $expenses2->add(new Expense(
            price: new Price(30.0, 'EUR'),
            what: 'Dinner',
            type: 'Non-Food',
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
            type: 'Groceries',
            location: 'Market'
        ));

        $expenses2 = Expenses::initial($user2Id, $user2Email);
        $expenses2->add(new Expense(
            price: new Price(25.0, 'EUR'),
            what: 'Lunch',
            type: 'Non-Food',
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
            type: 'Groceries',
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
            type: 'Groceries',
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
            type: 'Groceries',
            location: 'Market'
        ));
        $expenses1->add(new Expense(
            price: new Price(12.50, 'EUR'),
            what: 'Coffee',
            type: 'Groceries',
            location: 'Cafe'
        ));

        $expenses2 = Expenses::initial($user2Id, $user2Email);
        $expenses2->add(new Expense(
            price: new Price(10.25, 'EUR'),
            what: 'Lunch',
            type: 'Non-Food',
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
            type: 'Groceries',
            location: 'Apartment'
        ));

        $expenses2 = Expenses::initial($user2Id, $user2Email);
        $expenses2->add(new Expense(
            price: new Price(25.0, 'EUR'),
            what: 'Utilities split',
            type: 'Non-Food',
            location: 'Apartment'
        ));

        $compensation = Compensation::calculate($expenses1, $expenses2);

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
            type: 'Groceries',
            location: 'Market'
        ));
        $expenses1->add(new Expense(
            price: new Price(30.0, 'EUR'),
            what: 'Groceries',
            type: 'Groceries',
            location: 'Market'
        ));
        $expenses1->add(new Expense(
            price: new Price(20.0, 'EUR'),
            what: 'Gas',
            type: 'Non-Food',
            location: 'Gas Station'
        ));

        $expenses2 = Expenses::initial($user2Id, $user2Email);
        $expenses2->add(new Expense(
            price: new Price(40.0, 'EUR'),
            what: 'Lunch',
            type: 'Non-Food',
            location: 'Restaurant'
        ));

        $compensation = Compensation::calculate($expenses1, $expenses2);

        $this->assertSame($user2Email, $compensation->from);
        $this->assertSame($user1Email, $compensation->to);
        $this->assertSame(40.0, $compensation->settlement->value);
    }

    public function test_categories_filter_by_type(): void
    {
        $userEmail = 'user@example.com';
        $expenses = Expenses::initial('user-1', $userEmail);
        $expenses->add(new Expense(
            price: new Price(10.0, 'EUR'),
            what: 'Groceries',
            type: 'Groceries',
            location: 'Market'
        ));
        $expenses->add(new Expense(
            price: new Price(20.0, 'EUR'),
            what: 'Tool',
            type: 'Non-Food',
            location: 'Hardware'
        ));
        $expenses->add(new Expense(
            price: new Price(50.0, 'EUR'),
            what: 'Lent Money',
            type: 'Lent',
            location: 'Friend'
        ));

        $groceriesCategories = $expenses->categories(['Groceries']);
        $this->assertCount(1, $groceriesCategories);
        $this->assertSame(10.0, $groceriesCategories[0]->sum->value);

        $nonFoodCategories = $expenses->categories(['Non-Food']);
        $this->assertCount(1, $nonFoodCategories);
        $this->assertSame(20.0, $nonFoodCategories[0]->sum->value);

        $lentCategories = $expenses->categories(['Lent']);
        $this->assertCount(1, $lentCategories);
        $this->assertSame(50.0, $lentCategories[0]->sum->value);
    }

    public function test_categories_with_empty_filter_returns_all(): void
    {
        $userEmail = 'user@example.com';
        $expenses = Expenses::initial('user-1', $userEmail);
        $expenses->add(new Expense(
            price: new Price(10.0, 'EUR'),
            what: 'Groceries',
            type: 'Groceries',
            location: 'Market'
        ));
        $expenses->add(new Expense(
            price: new Price(20.0, 'EUR'),
            what: 'Tool',
            type: 'Non-Food',
            location: 'Hardware'
        ));
        $expenses->add(new Expense(
            price: new Price(50.0, 'EUR'),
            what: 'Lent Money',
            type: 'Lent',
            location: 'Friend'
        ));

        $allCategories = $expenses->categories();
        $this->assertCount(3, $allCategories);
    }

    public function test_categories_filter_multiple_types(): void
    {
        $userEmail = 'user@example.com';
        $expenses = Expenses::initial('user-1', $userEmail);
        $expenses->add(new Expense(
            price: new Price(10.0, 'EUR'),
            what: 'Groceries',
            type: 'Groceries',
            location: 'Market'
        ));
        $expenses->add(new Expense(
            price: new Price(20.0, 'EUR'),
            what: 'Tool',
            type: 'Non-Food',
            location: 'Hardware'
        ));
        $expenses->add(new Expense(
            price: new Price(50.0, 'EUR'),
            what: 'Lent Money',
            type: 'Lent',
            location: 'Friend'
        ));

        $filtered = $expenses->categories(['Groceries', 'Lent']);
        $this->assertCount(2, $filtered);
    }

    public function test_compensation_with_lent_money_from_first_user(): void
    {
        $user1Id = 'user-1';
        $user1Email = 'user1@example.com';
        $user2Id = 'user-2';
        $user2Email = 'user2@example.com';

        $expenses1 = Expenses::initial($user1Id, $user1Email);
        $expenses1->add(new Expense(
            price: new Price(50.0, 'EUR'),
            what: 'Money Lent',
            type: 'Lent',
            location: 'Transfer'
        ));

        $expenses2 = Expenses::initial($user2Id, $user2Email);

        $compensation = Compensation::calculate($expenses1, $expenses2);

        $this->assertSame($user2Email, $compensation->from);
        $this->assertSame($user1Email, $compensation->to);
        $this->assertSame(50.0, $compensation->settlement->value);
    }

    public function test_compensation_with_lent_money_from_second_user(): void
    {
        $user1Id = 'user-1';
        $user1Email = 'user1@example.com';
        $user2Id = 'user-2';
        $user2Email = 'user2@example.com';

        $expenses1 = Expenses::initial($user1Id, $user1Email);

        $expenses2 = Expenses::initial($user2Id, $user2Email);
        $expenses2->add(new Expense(
            price: new Price(75.0, 'EUR'),
            what: 'Money Lent',
            type: 'Lent',
            location: 'Transfer'
        ));

        $compensation = Compensation::calculate($expenses1, $expenses2);

        $this->assertSame($user1Email, $compensation->from);
        $this->assertSame($user2Email, $compensation->to);
        $this->assertSame(75.0, $compensation->settlement->value);
    }

    public function test_compensation_with_both_lent_and_spent(): void
    {
        $user1Id = 'user-1';
        $user1Email = 'user1@example.com';
        $user2Id = 'user-2';
        $user2Email = 'user2@example.com';

        $expenses1 = Expenses::initial($user1Id, $user1Email);
        $expenses1->add(new Expense(
            price: new Price(60.0, 'EUR'),
            what: 'Groceries',
            type: 'Groceries',
            location: 'Market'
        ));
        $expenses1->add(new Expense(
            price: new Price(100.0, 'EUR'),
            what: 'Money Lent',
            type: 'Lent',
            location: 'Transfer'
        ));

        $expenses2 = Expenses::initial($user2Id, $user2Email);
        $expenses2->add(new Expense(
            price: new Price(40.0, 'EUR'),
            what: 'Groceries',
            type: 'Groceries',
            location: 'Market'
        ));

        $compensation = Compensation::calculate($expenses1, $expenses2);

        $this->assertSame($user2Email, $compensation->from);
        $this->assertSame($user1Email, $compensation->to);
        $this->assertSame(120.0, $compensation->settlement->value);
    }

    public function test_compensation_with_lent_amounts_from_both_users(): void
    {
        $user1Id = 'user-1';
        $user1Email = 'user1@example.com';
        $user2Id = 'user-2';
        $user2Email = 'user2@example.com';

        $expenses1 = Expenses::initial($user1Id, $user1Email);
        $expenses1->add(new Expense(
            price: new Price(50.0, 'EUR'),
            what: 'Groceries',
            type: 'Groceries',
            location: 'Market'
        ));
        $expenses1->add(new Expense(
            price: new Price(100.0, 'EUR'),
            what: 'Money Lent',
            type: 'Lent',
            location: 'Transfer'
        ));

        $expenses2 = Expenses::initial($user2Id, $user2Email);
        $expenses2->add(new Expense(
            price: new Price(50.0, 'EUR'),
            what: 'Groceries',
            type: 'Groceries',
            location: 'Market'
        ));
        $expenses2->add(new Expense(
            price: new Price(30.0, 'EUR'),
            what: 'Money Lent',
            type: 'Lent',
            location: 'Transfer'
        ));

        $compensation = Compensation::calculate($expenses1, $expenses2);

        $this->assertSame($user2Email, $compensation->from);
        $this->assertSame($user1Email, $compensation->to);
        $this->assertSame(70.0, $compensation->settlement->value);
    }

    public function test_compensation_lent_adds_to_spent_difference(): void
    {
        $user1Id = 'user-1';
        $user1Email = 'user1@example.com';
        $user2Id = 'user-2';
        $user2Email = 'user2@example.com';

        $expenses1 = Expenses::initial($user1Id, $user1Email);
        $expenses1->add(new Expense(
            price: new Price(60.0, 'EUR'),
            what: 'Groceries',
            type: 'Groceries',
            location: 'Market'
        ));
        $expenses1->add(new Expense(
            price: new Price(20.0, 'EUR'),
            what: 'Money Lent',
            type: 'Lent',
            location: 'Transfer'
        ));

        $expenses2 = Expenses::initial($user2Id, $user2Email);
        $expenses2->add(new Expense(
            price: new Price(40.0, 'EUR'),
            what: 'Groceries',
            type: 'Groceries',
            location: 'Market'
        ));

        $compensation = Compensation::calculate($expenses1, $expenses2);

        // Spent diff: 60 - 40 = 20 (User 1 spent 20 more)
        // Lent diff: 20 - 0 = 20 (User 1 lent 20 more)
        // Total: 20 + 20 = 40
        $this->assertSame($user2Email, $compensation->from);
        $this->assertSame($user1Email, $compensation->to);
        $this->assertSame(40.0, $compensation->settlement->value);
    }
}
