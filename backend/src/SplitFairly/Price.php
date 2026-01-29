<?php

declare(strict_types=1);

namespace App\SplitFairly;

use App\Invariant\Ensure;

final readonly class Price implements \Stringable
{
    public function __construct(
        public float $value,
        public string $currency = 'EUR',
    ) {
        Ensure::that(!empty($currency));
    }

    public static function ZERO(): self
    {
        return new self(0.0, 'EUR');
    }

    public function add(self $other): self
    {
        Ensure::that($this->currency === $other->currency);

        return new self($this->value + $other->value, $this->currency);
    }

    public function __toString(): string
    {
        return sprintf('%.2f %s', $this->value, $this->currency);
    }
}
