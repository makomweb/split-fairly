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

    public static function ABS(self $price): self
    {
        return new self(abs($price->value), $price->currency);
    }

    public static function ZERO(string $currency = 'EUR'): self
    {
        return new self(0.0, $currency);
    }

    public function add(self $other): self
    {
        Ensure::that(
            $this->currency === $other->currency,
            sprintf('Currencies %s and %s must be the same!', $this->currency, $other->currency)
        );

        return new self($this->value + $other->value, $this->currency);
    }

    public function substract(self $other): self
    {
        Ensure::that(
            $this->currency === $other->currency,
            sprintf('Currencies %s and %s must be the same!', $this->currency, $other->currency)
        );

        return new self($this->value - $other->value, $this->currency);
    }

    public function divide(float $divisor): self
    {
        Ensure::that($divisor > 0);

        return new self($this->value / $divisor, $this->currency);
    }

    public function __toString(): string
    {
        return sprintf('%.2f %s', $this->value, $this->currency);
    }
}
