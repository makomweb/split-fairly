<?php

namespace App\Controller\API;

use App\Controller\DTO\Expense;
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
    ) {
    }

    #[Route('/track', name: 'track', methods: ['POST'])]
    public function track(
        #[MapRequestPayload] Expense $expense,
    ): JsonResponse {
        $this->logger->debug('Entering '.__METHOD__);

        $this->logger->info(sprintf(
            '💰 Expense tracked: "%s" spent %s',
            $expense->user,
            $expense->price,
        ), ['expense' => $expense]);

        return $this->json($expense);
    }
}
