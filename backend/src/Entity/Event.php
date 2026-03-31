<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

#[ORM\Entity]
class Event
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $id;

    /** @param array<string,mixed> $payload */
    public function __construct(
        #[ORM\Column]
        private string $createdBy,
        #[ORM\Column]
        private string $subjectType,
        #[ORM\Column]
        private string $subjectId,
        #[ORM\Column]
        private string $eventType,
        #[ORM\Column]
        private array $payload = [],
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable('now'),
    ) {
        $this->id = new UuidV7();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getCreatedBy(): string
    {
        return $this->createdBy;
    }

    public function getSubjectType(): string
    {
        return $this->subjectType;
    }

    public function getSubjectId(): string
    {
        return $this->subjectId;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }

    /** @return array<string,mixed> */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /** @param array<string,mixed> $payload */
    public function setPayload(array $payload): void
    {
        $this->payload = $payload;
    }

    public function getValue(string $key): mixed
    {
        return $this->payload[$key];
    }
}
