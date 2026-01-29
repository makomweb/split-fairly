<?php

declare(strict_types=1);

namespace App\SplitFairly\DTO;

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
     * return Category[]
     */
    public function categories(): array
    {
        return array_reduce(
            $this->expenses,
            /**
             * @param Category[] $carry
             * @return Category[]
             */
            static function (array $carry, Expense $expense): array {
                // TODO sum up the "what" to calculate each category
                return $carry;
            },
            /** @var Category[] */
            []
        );
    }
}
