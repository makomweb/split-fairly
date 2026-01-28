<?php

declare(strict_types=1);

namespace App\Controller\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class Expense
{
    public function __construct(
        #[Assert\NotBlank]
        public \DateTimeImmutable $time,

        #[Assert\NotBlank]
        public string $user,

        #[Assert\NotBlank]
        #[Assert\Valid]
        public Price $price,

        #[Assert\NotBlank]
        public string $what,

        #[Assert\NotBlank]
        public string $location,
    ) {
    }
}
