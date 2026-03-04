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

        self::assertSame($user2Email, $compensation->from);
        self::assertSame($user1Email, $compensation->to);
        // User1 spent 50, so owes 25. User2 spent 25, so owes 12.5. Difference: 25 - 12.5 = 12.5
        self::assertSame(12.5, $compensation->settlement->value);
        self::assertSame('EUR', $compensation->settlement->currency);
        self::assertSame(
            sprintf('From: "%s" - to: "%s" - price: "%s"', $compensation->from, $compensation->to, (string) $compensation->settlement),
            (string) $compensation
        );
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

        self::assertSame($user1Email, $compensation->from);
        self::assertSame($user2Email, $compensation->to);
        // User1 spent 15, so owes 7.5. User2 spent 50, so owes 25. Difference: 25 - 7.5 = 17.5
        self::assertSame(17.5, $compensation->settlement->value);
        self::assertSame('EUR', $compensation->settlement->currency);
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

        self::assertSame(0.0, $compensation->settlement->value);
        self::assertSame('EUR', $compensation->settlement->currency);
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

        self::assertSame($user1Email, $compensation->from);
        self::assertSame($user2Email, $compensation->to);
        // User1 spent 0, so owes 0. User2 spent 50, so owes 25. Difference: 25 - 0 = 25
        self::assertSame(25.0, $compensation->settlement->value);
        self::assertSame('EUR', $compensation->settlement->currency);
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

        self::assertSame($user2Email, $compensation->from);
        self::assertSame($user1Email, $compensation->to);
        // User1 spent 75, so owes 37.5. User2 spent 0, so owes 0. Difference: 37.5 - 0 = 37.5
        self::assertSame(37.5, $compensation->settlement->value);
        self::assertSame('EUR', $compensation->settlement->currency);
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

        self::assertSame(0.0, $compensation->settlement->value);
        self::assertSame('EUR', $compensation->settlement->currency);
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

        self::assertSame($user2Email, $compensation->from);
        self::assertSame($user1Email, $compensation->to);
        // User1 spent 28.25, so owes 14.125. User2 spent 10.25, so owes 5.125. Difference: 14.125 - 5.125 = 9.0
        self::assertSame(9.0, $compensation->settlement->value);
        self::assertSame('EUR', $compensation->settlement->currency);
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

        // User1 spent 100, so owes 50. User2 spent 25, so owes 12.5. Difference: 50 - 12.5 = 37.5
        self::assertSame(37.5, $compensation->settlement->value);
        self::assertGreaterThan(0, $compensation->settlement->value);
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

        self::assertSame($user2Email, $compensation->from);
        self::assertSame($user1Email, $compensation->to);
        // User1 spent 80, so owes 40. User2 spent 40, so owes 20. Difference: 40 - 20 = 20
        self::assertSame(20.0, $compensation->settlement->value);
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
        self::assertCount(1, $groceriesCategories);
        self::assertSame(10.0, $groceriesCategories[0]->sum->value);

        $nonFoodCategories = $expenses->categories(['Non-Food']);
        self::assertCount(1, $nonFoodCategories);
        self::assertSame(20.0, $nonFoodCategories[0]->sum->value);

        $lentCategories = $expenses->categories(['Lent']);
        self::assertCount(1, $lentCategories);
        self::assertSame(50.0, $lentCategories[0]->sum->value);
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
        self::assertCount(3, $allCategories);
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
        self::assertCount(2, $filtered);
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

        self::assertSame($user2Email, $compensation->from);
        self::assertSame($user1Email, $compensation->to);
        self::assertSame(50.0, $compensation->settlement->value);
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

        self::assertSame($user1Email, $compensation->from);
        self::assertSame($user2Email, $compensation->to);
        self::assertSame(75.0, $compensation->settlement->value);
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

        self::assertSame($user2Email, $compensation->from);
        self::assertSame($user1Email, $compensation->to);
        // User1 spent 60, so owes 30. User2 spent 40, so owes 20. Spent diff: 30 - 20 = 10
        // User1 lent 100, User2 lent 0. Lent diff: 100 - 0 = 100
        // Total: 10 + 100 = 110
        self::assertSame(110.0, $compensation->settlement->value);
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

        self::assertSame($user2Email, $compensation->from);
        self::assertSame($user1Email, $compensation->to);
        // User1 spent 50, so owes 25. User2 spent 50, so owes 25. Spent diff: 25 - 25 = 0
        // User1 lent 100, User2 lent 30. Lent diff: 100 - 30 = 70
        // Total: 0 + 70 = 70
        self::assertSame(70.0, $compensation->settlement->value);
    }

    public function test_compensation_with_filtered_types_groceries_only(): void
    {
        $user1Id = 'user-1';
        $user1Email = 'user1@example.com';
        $user2Id = 'user-2';
        $user2Email = 'user2@example.com';

        $expenses1 = Expenses::initial($user1Id, $user1Email);
        $expenses1->add(new Expense(
            price: new Price(100.0, 'EUR'),
            what: 'Groceries',
            type: 'Groceries',
            location: 'Market'
        ));
        $expenses1->add(new Expense(
            price: new Price(50.0, 'EUR'),
            what: 'Non-Food',
            type: 'Non-Food',
            location: 'Store'
        ));
        $expenses1->add(new Expense(
            price: new Price(20.0, 'EUR'),
            what: 'Cash',
            type: 'Lent',
            location: 'user2@example.com'
        ));

        $expenses2 = Expenses::initial($user2Id, $user2Email);
        $expenses2->add(new Expense(
            price: new Price(40.0, 'EUR'),
            what: 'Groceries',
            type: 'Groceries',
            location: 'Market'
        ));

        // Include only Groceries
        $compensation = Compensation::calculate($expenses1, $expenses2, ['Groceries']);

        // Spent diff: 100/2 - 40/2 = 50 - 20 = 30 (User 1 spent 30 more)
        // Lent diff: 0 (not included)
        // Non-Food: 0 (not included)
        // Total: 30
        self::assertSame($user2Email, $compensation->from);
        self::assertSame($user1Email, $compensation->to);
        self::assertSame(30.0, $compensation->settlement->value);
    }

    public function test_compensation_with_filtered_types_lent_only(): void
    {
        $user1Id = 'user-1';
        $user1Email = 'user1@example.com';
        $user2Id = 'user-2';
        $user2Email = 'user2@example.com';

        $expenses1 = Expenses::initial($user1Id, $user1Email);
        $expenses1->add(new Expense(
            price: new Price(100.0, 'EUR'),
            what: 'Groceries',
            type: 'Groceries',
            location: 'Market'
        ));
        $expenses1->add(new Expense(
            price: new Price(50.0, 'EUR'),
            what: 'Cash',
            type: 'Lent',
            location: 'user2@example.com'
        ));

        $expenses2 = Expenses::initial($user2Id, $user2Email);
        $expenses2->add(new Expense(
            price: new Price(40.0, 'EUR'),
            what: 'Groceries',
            type: 'Groceries',
            location: 'Market'
        ));
        $expenses2->add(new Expense(
            price: new Price(10.0, 'EUR'),
            what: 'Cash',
            type: 'Lent',
            location: 'user1@example.com'
        ));

        // Include only Lent
        $compensation = Compensation::calculate($expenses1, $expenses2, ['Lent']);

        // Spent diff: 0 (not included)
        // Lent diff: 50 - 10 = 40 (User 1 lent 40 more)
        // Total: 40
        self::assertSame($user2Email, $compensation->from);
        self::assertSame($user1Email, $compensation->to);
        self::assertSame(40.0, $compensation->settlement->value);
    }

    public function test_compensation_with_filtered_types_no_types(): void
    {
        $user1Id = 'user-1';
        $user1Email = 'user1@example.com';
        $user2Id = 'user-2';
        $user2Email = 'user2@example.com';

        $expenses1 = Expenses::initial($user1Id, $user1Email);
        $expenses1->add(new Expense(
            price: new Price(100.0, 'EUR'),
            what: 'Groceries',
            type: 'Groceries',
            location: 'Market'
        ));

        $expenses2 = Expenses::initial($user2Id, $user2Email);
        $expenses2->add(new Expense(
            price: new Price(50.0, 'EUR'),
            what: 'Groceries',
            type: 'Groceries',
            location: 'Market'
        ));

        // Include no types (empty array)
        $compensation = Compensation::calculate($expenses1, $expenses2, []);

        // No types included, so no compensation needed
        self::assertSame(0.0, $compensation->settlement->value);
    }
}
