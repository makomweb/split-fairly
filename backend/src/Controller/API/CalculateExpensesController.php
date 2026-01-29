<?php

namespace App\Controller\API;

use App\SplitFairly\Calculator;
use App\SplitFairly\Expenses;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api.')]
class CalculateExpensesController extends AbstractController
{
    public function __construct(
        private readonly Calculator $calculator,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/calculate', name: 'calculate', methods: ['GET'])]
    public function calculate(): JsonResponse
    {
        $expenses = $this->calculator->calculate();
        $compensate = $expenses[0]->substract($expenses[1]);

        $this->logger->debug('Calculated', ['expenses' => $expenses, 'compensate' => $compensate]);

        return $this->json(
            array_map(
                static fn (Expenses $e) => [
                    'user_email' => $e->userEmail,
                    'categories' => $e->categories(),
                ],
                $expenses
            )
        );
    }
}
