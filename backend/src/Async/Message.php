<?php

declare(strict_types=1);

namespace App\Async;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(transport: 'async')]
final class Message
{
    private function __construct(
        public readonly float $createdAt,
        public readonly string $type,
    ) {
    }

    public static function create(string $type): self
    {
        return new self(microtime(true), $type);
    }
}
