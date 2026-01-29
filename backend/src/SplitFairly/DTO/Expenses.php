<?php

declare(strict_types=1);

namespace App\SplitFairly\DTO;

final readonly class Expenses
{
    /**
     * @param Expense[] $expenses
     */
    public function __construct(
        public string $userId,
        public array $expenses,
    ) {
    }

    public function withExpense(Expense $expense): self
    {
        return new self($this->userId, [...$this->expenses, $expense]);
    }
}
