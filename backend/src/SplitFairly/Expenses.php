<?php

declare(strict_types=1);

namespace App\SplitFairly;

final class Expenses
{
    /**
     * @param Expense[] $expenses
     */
    private function __construct(
        public readonly string $userUuid,
        public readonly string $userEmail,
        public array $expenses,
    ) {
    }

    public static function initial(string $userUuid, string $email): self
    {
        return new self($userUuid, $email, []);
    }

    public function add(Expense $expense): void
    {
        $this->expenses[] = $expense;
    }

    /**
     * @param string[] $filter
     *
     * @return array<int, Category>
     */
    public function categories(array $filter = [/* no filter */]): array
    {
        /** @var array<int, Category> $result */
        $result = array_reduce(
            $this->expenses,
            /**
             * @param array<int, Category> $carry
             *
             * @return array<int, Category>
             */
            static function (array $carry, Expense $expense) use ($filter): array {
                if (!empty($filter) && !in_array($expense->type, $filter, true)) {
                    return $carry;
                }

                $found = null;
                $foundKey = null;

                foreach ($carry as $key => $category) {
                    assert($category instanceof Category);
                    if ($category->type === $expense->type) {
                        $found = $category;
                        $foundKey = $key;
                        break;
                    }
                }

                if ($found instanceof Category && null !== $foundKey) {
                    $carry[$foundKey] = $found->with($expense);
                } else {
                    $carry[] = Category::initial($expense);
                }

                return $carry;
            },
            []
        );

        return $result;
    }

    public function spent(): Price
    {
        return array_reduce(
            $this->categories(['Groceries', 'Non-Food']),
            static fn (Price $spent, Category $category) => $spent->add($category->sum),
            Price::ZERO()
        );
    }

    public function lent(): Price
    {
        return array_reduce(
            $this->categories(['Lent']),
            static fn (Price $spent, Category $category) => $spent->add($category->sum),
            Price::ZERO()
        );
    }
}
