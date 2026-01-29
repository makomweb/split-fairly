<?php

declare(strict_types=1);

namespace App\SplitFairly\DTO;

use App\Invariant\Ensure;

final readonly class Price implements \Stringable
{
    public function __construct(
        public float $value,
        public string $currency,
    ) {
        Ensure::that(!empty($currency));
    }

    public function __toString(): string
    {
        return sprintf('%.2f %s', $this->value, $this->currency);
    }
}
