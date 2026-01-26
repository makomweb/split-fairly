<?php

declare(strict_types=1);

namespace App\Invariant;

final readonly class Ensure
{
    public static function that(bool $condition, ?string $error = null): void
    {
        if (!$condition) {
            if (is_string($error)) {
                throw new InvariantException($error);
            }

            throw new InvariantException('Ensure failed!');
        }
    }
}
