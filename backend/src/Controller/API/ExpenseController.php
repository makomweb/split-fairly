<?php

namespace App\Controller\API;

use App\Controller\DTO\Expense;
use App\SplitFairly\CurrentUserInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api.')]
class ExpenseController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly CurrentUserInterface $currentUser,
    ) {
    }

    #[Route('/track', name: 'track', methods: ['POST'])]
    public function track(
        #[MapRequestPayload] Expense $expense,
    ): JsonResponse {
        $this->logger->info(sprintf(
            '💰 Expense tracked: "%s" spent %s',
            $this->currentUser->getEmail(),
            $expense->price,
        ), ['expense' => $expense]);

        return $this->json($expense);
    }
}
