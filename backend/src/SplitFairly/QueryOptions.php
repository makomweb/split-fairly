<?php

declare(strict_types=1);

namespace App\SplitFairly;

/**
 * Options to query the event store.
 */
final readonly class QueryOptions
{
    /**
     * @param string[] $createdBy
     * @param string[] $subjectTypes
     * @param string[] $subjectIds
     * @param string[] $eventTypes
     */
    public function __construct(
        public array $createdBy = [],
        public array $subjectTypes = [],
        public array $subjectIds = [],
        public array $eventTypes = [],
    ) {
    }

    public function isEmpty(): bool
    {
        return empty($this->createdBy)
            && empty($this->subjectTypes)
            && empty($this->subjectIds)
            && empty($this->eventTypes);
    }
}
