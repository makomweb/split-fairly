<?php

declare(strict_types=1);

namespace App\SplitFairly\DTO;

use App\Invariant\Ensure;

final class Category
{
    private function __construct(
        public readonly string $what,
        public readonly Price $sum,
    ) {
    }

    public static function initial(Expense $expense): self
    {
        return new self($expense->what, $expense->price);
    }

    public function with(Expense $expense): self
    {
        Ensure::that($this->what === $expense->what);

        return new self($this->what, $this->sum->add($expense->price));
    }
}
