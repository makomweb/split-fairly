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
        public string $type,
        public string $location,
    ) {
        Ensure::that('' !== $what && '0' !== $what);
        Ensure::that('' !== $type && '0' !== $type);
        Ensure::that('' !== $location && '0' !== $location);
        Ensure::that(
            in_array($type, array_map(static fn (ExpenseType $e) => $e->value, ExpenseType::cases()), strict: true),
            sprintf('Invalid expense type: %s', $type)
        );
    }

    public function getId(): Uuid
    {
        return Uuid::v5(
            Uuid::fromString(Uuid::NAMESPACE_OID),
            sprintf('%s - %s - %s - %s', $this->price, $this->what, $this->type, $this->location)
        );
    }
}
