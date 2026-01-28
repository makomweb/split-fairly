<?php

declare(strict_types=1);

namespace App\Controller\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class Price implements \Stringable
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Positive]
        public float $value,

        #[Assert\NotBlank]
        #[Assert\Currency]
        public string $currency,
    ) {
    }

    public function __toString(): string
    {
        return sprintf('%.2f %s', $this->value, $this->currency);
    }
}
