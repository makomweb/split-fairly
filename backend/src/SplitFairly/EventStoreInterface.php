<?php

declare(strict_types=1);

namespace App\SplitFairly;

interface EventStoreInterface
{
    /** @return Event[] */
    public function getEvents(QueryOptions $options = new QueryOptions()): array;

    /**
     * Get all unique user IDs who have created events.
     *
     * @return string[]
     */
    public function getUserIds(): array;

    /**
     * @param $dontCommit Don't commit yet. Make sure you commit at least once after a sequence of events has been persisted.
     */
    public function persist(Event $event, bool $dontCommit = false): void;

    public function reset(): void;
}
