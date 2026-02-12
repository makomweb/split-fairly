<?php

declare(strict_types=1);

namespace App\Tests\Unit\SplitFairly;

use App\SplitFairly\QueryOptions;
use PHPUnit\Framework\TestCase;

final class QueryOptionsTest extends TestCase
{
    public function test_constructor_sets_properties(): void
    {
        $createdBy = ['user-1', 'user-2'];
        $subjectTypes = ['Expense'];
        $subjectIds = ['exp-1', 'exp-2'];
        $eventTypes = ['tracked'];

        $options = new QueryOptions(
            createdBy: $createdBy,
            subjectTypes: $subjectTypes,
            subjectIds: $subjectIds,
            eventTypes: $eventTypes
        );

        self::assertSame($createdBy, $options->createdBy);
        self::assertSame($subjectTypes, $options->subjectTypes);
        self::assertSame($subjectIds, $options->subjectIds);
        self::assertSame($eventTypes, $options->eventTypes);
    }

    public function test_is_empty_returns_true_for_empty(): void
    {
        $options = new QueryOptions();
        self::assertTrue($options->isEmpty());
    }

    public function test_is_empty_returns_false_for_non_empty(): void
    {
        $options = new QueryOptions(createdBy: ['user-1']);
        self::assertFalse($options->isEmpty());
    }
}
