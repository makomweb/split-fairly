<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class Expense
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\DateTime]
        public string $time,

        #[Assert\NotBlank]
        public string $user,

        #[Assert\NotBlank]
        public string $what,

        #[Assert\NotBlank]
        public string $location,

        #[Assert\NotBlank]
        #[Assert\Positive]
        public float $price,

        #[Assert\NotBlank]
        public string $purpose,
    ) {
    }
}
