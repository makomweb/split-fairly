<?php

declare(strict_types=1);

namespace App\SplitFairly;

use App\Invariant\Ensure;
use Symfony\Component\Uid\Uuid;

final readonly class Expense
{
    public function __construct(
        public Price $price,
        public string $what,
        public string $type = 'Groceries',
        public string $location,
    ) {
        Ensure::that(!empty($what));
        Ensure::that(!empty($type));
        Ensure::that(!empty($location));
    }

    public function getId(): Uuid
    {
        return Uuid::v5(
            Uuid::fromString(Uuid::NAMESPACE_OID),
            sprintf('%s - %s - %s - %s', $this->price, $this->what, $this->type, $this->location)
        );
    }
}
