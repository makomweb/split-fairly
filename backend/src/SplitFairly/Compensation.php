<?php

declare(strict_types=1);

namespace App\SplitFairly;

final class Compensation implements \Stringable
{
    private function __construct(
        public readonly string $from,
        public readonly string $to,
        public Price $settlement,
    ) {
    }

    /**
     * @param array<string> $includeTypes
     */
    public static function calculate(Expenses $a, Expenses $b, array $includeTypes = ['Groceries', 'Non-Food', 'Lent']): self
    {
        // Determine which amounts to include based on filters
        $spentTypes = array_intersect($includeTypes, ['Groceries', 'Non-Food']);
        $lentTypes = array_intersect($includeTypes, ['Lent']);

        $spentA = $spentTypes === [] ? Price::ZERO() : $a->spent($spentTypes)->divide(2);
        $spentB = $spentTypes === [] ? Price::ZERO() : $b->spent($spentTypes)->divide(2);
        $spentDiff = $spentA->substract($spentB);

        $lentA = $lentTypes === [] ? Price::ZERO() : $a->lent($lentTypes);
        $lentB = $lentTypes === [] ? Price::ZERO() : $b->lent($lentTypes);
        $lentDiff = $lentA->substract($lentB);

        $totalDiff = $spentDiff->add($lentDiff);

        return new Compensation(
            from: $totalDiff->value > 0 ? $b->userEmail : $a->userEmail,
            to: $totalDiff->value > 0 ? $a->userEmail : $b->userEmail,
            settlement: Price::ABS($totalDiff)
        );
    }

    public function __toString(): string
    {
        return sprintf('From: "%s" - to: "%s" - price: "%s"', $this->from, $this->to, $this->settlement);
    }
}
