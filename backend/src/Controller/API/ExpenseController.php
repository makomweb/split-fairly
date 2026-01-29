<?php

namespace App\Controller\API;

use App\SplitFairly\DTO\Expense;
use App\SplitFairly\ExpenseTracker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api.')]
class ExpenseController extends AbstractController
{
    public function __construct(
        private readonly ExpenseTracker $tracker,
    ) {
    }

    #[Route('/track', name: 'track', methods: ['POST'])]
    public function track(#[MapRequestPayload] Expense $expense): JsonResponse
    {
        $this->tracker->track($expense);

        return $this->json($expense);
    }
}
