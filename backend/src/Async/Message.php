<?php

declare(strict_types=1);

namespace App\Async;

use App\Entity\Event;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(transport: 'async')]
final class Message
{
    /** @var array<string,string> */
    private array $traceContext;

    private function __construct(
        public readonly Event $event,
        public readonly float $createdAt,
    ) {
        $this->traceContext = [];
    }

    public static function fromEvent(Event $event): self
    {
        return new self($event, microtime(true));
    }

    public function addTraceContext(string $key, string $value): void
    {
        $this->traceContext[$key] = $value;
    }

    /** @return array<string,string> */
    public function getTraceContext(): array
    {
        return $this->traceContext;
    }
}
