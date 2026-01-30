<?php

declare(strict_types=1);

namespace App\SplitFairly;

use App\Invariant\Ensure;

final class Category
{
    private function __construct(
        public readonly string $type,
        public readonly Price $sum,
    ) {
    }

    public static function initial(Expense $expense): self
    {
        return new self($expense->type, $expense->price);
    }

    public function with(Expense $expense): self
    {
        Ensure::that($this->type === $expense->type);

        return new self($this->type, $this->sum->add($expense->price));
    }
}
