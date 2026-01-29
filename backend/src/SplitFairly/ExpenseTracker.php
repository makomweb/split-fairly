<?php

declare(strict_types=1);

namespace App\SplitFairly;

use App\SplitFairly\DTO\Expense;
use Psr\Log\LoggerInterface;

final readonly class ExpenseTracker
{
    public function __construct(
        private readonly CurrentUserInterface $currentUser,
        private readonly EventStoreInterface $eventStore,
        private readonly NormalizerInterface $normalizer,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function track(Expense $expense): void
    {
        $this->eventStore->persist(
            event: new Event(
                subjectType: 'expense',
                subjectId: $expense->getId()->toRfc4122(),
                eventType: 'tracked',
                payload: $this->normalizer->toArray($expense, ['id'])
            ),
            dontCommit: false
        );

        $this->logger->info(sprintf(
            '💰 Expense tracked: "%s" spent %s',
            $this->currentUser->getEmail(),
            $expense->price,
        ), ['expense' => $expense]);
    }
}
