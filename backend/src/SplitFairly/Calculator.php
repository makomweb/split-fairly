<?php

declare(strict_types=1);

namespace App\SplitFairly;

use App\SplitFairly\DTO\Expenses;

final readonly class Calculator
{
    public function __construct(
        private readonly EventStoreInterface $eventStore,
        private readonly DenormalizerInterface $denormalizer,
    ) {
    }

    /**
     * @return Expenses[]
     */
    public function calculate(): array
    {
        $all = $this->eventStore->getEvents();
        
        // TODO: implement collecting expenses per user ("created_by")!

        return [];
    }
}
