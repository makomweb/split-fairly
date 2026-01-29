<?php

declare(strict_types=1);

namespace App\SplitFairly;

interface NormalizerInterface
{
    /**
     * Normalize an object into an array structure of key value pairs.
     *
     * @param array<string> $ignoreFields
     *
     * @return array<string,mixed>
     */
    public function toArray(mixed $object, array $ignoreFields = []): array;
}
