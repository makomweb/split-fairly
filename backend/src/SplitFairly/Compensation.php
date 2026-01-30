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
        $diff = $a->spent()->substract($b->spent());

        return new Compensation(
            from: $diff->value > 0 ? $b->userEmail : $a->userEmail,
            to: $diff->value > 0 ? $a->userEmail : $b->userEmail,
            settlement: Price::ABS($diff)
        );
    }
}
