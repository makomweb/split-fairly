<?php

declare(strict_types=1);

namespace App\Tests\Unit\SplitFairly;

use App\SplitFairly\Event;
use PHPUnit\Framework\TestCase;

final class EventTest extends TestCase
{
    public function test_constructor_sets_properties(): void
    {
        $createdBy = 'user-1';
        $subjectType = 'Expense';
        $subjectId = 'exp-1';
        $eventType = 'tracked';
        $payload = ['price' => 10.0, 'currency' => 'EUR'];
        $createdAt = new \DateTimeImmutable('2026-02-12T07:00:00Z');

        $event = new Event(
            createdBy: $createdBy,
            subjectType: $subjectType,
            subjectId: $subjectId,
            eventType: $eventType,
            payload: $payload,
            createdAt: $createdAt
        );

        self::assertSame($createdBy, $event->createdBy);
        self::assertSame($subjectType, $event->subjectType);
        self::assertSame($subjectId, $event->subjectId);
        self::assertSame($eventType, $event->eventType);
        self::assertSame($payload, $event->payload);
        self::assertSame($createdAt, $event->createdAt);
    }

    public function test_get_value_returns_payload_value(): void
    {
        $event = new Event(
            createdBy: 'user-1',
            subjectType: 'Expense',
            subjectId: 'exp-1',
            eventType: 'tracked',
            payload: ['price' => 10.0, 'currency' => 'EUR'],
        );

        self::assertSame(10.0, $event->getValue('price'));
        self::assertSame('EUR', $event->getValue('currency'));
    }
}
