<?php

declare(strict_types=1);

namespace App\SplitFairly;

final class Compensation
{
    private function __construct(
        public readonly string $from,
        public readonly string $to,
        public Price $settlement,
    ) {
    }

    public static function calculate(Expenses $a, Expenses $b): self
    {
        $spentDiff = $a->spent()->substract($b->spent());
        $lentDiff = $a->lent()->substract($b->lent());
        
        $totalDiff = $spentDiff->add($lentDiff);

        return new Compensation(
            from: $totalDiff->value > 0 ? $b->userEmail : $a->userEmail,
            to: $totalDiff->value > 0 ? $a->userEmail : $b->userEmail,
            settlement: Price::ABS($totalDiff)
        );
    }
}
