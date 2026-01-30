<?php

declare(strict_types=1);

namespace App\SplitFairly;

final readonly class Event
{
    /** @param array<string,mixed> $payload */
    public function __construct(
        public string $createdBy,
        public string $subjectType,
        public string $subjectId,
        public string $eventType,
        public array $payload,
        public \DateTimeImmutable $createdAt = new \DateTimeImmutable('now'),
    ) {
    }

    public function getValue(string $key): mixed
    {
        return $this->payload[$key];
    }
}
