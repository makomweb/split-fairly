<?php

declare(strict_types=1);

namespace App\SplitFairly\DTO;

final class Expenses
{
    /**
     * @param Expense[] $expenses
     */
    private function __construct(
        public readonly string $userId,
        public array $expenses,
    ) {
    }

    public static function initial(string $userId): self
    {
        return new self($userId, []);
    }

    public function add(Expense $expense): void
    {
        $this->expenses[] = $expense;
    }
}
