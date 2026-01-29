<?php

namespace App\Controller\API;

use App\Invariant\Ensure;
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
        
        Ensure::that(count($expenses) === 2);

        $compensation = $expenses[0]->substract($expenses[1]);

        $this->logger->debug('Calculated', ['expenses' => $expenses, 'compensation' => $compensation]);

        return $this->json([
            'users' => array_map(
                static fn (Expenses $e) => [
                    'user_email' => $e->userEmail,
                    'categories' => $e->categories(),
                ],
                $expenses
            ),
            'compensation' => $compensation ? [
                'value' => $compensation->value,
                'currency' => $compensation->currency,
                'from' => $compensation->value > 0 ? $expenses[1]->userEmail : $expenses[0]->userEmail,
                'to' => $compensation->value > 0 ? $expenses[0]->userEmail : $expenses[1]->userEmail,
                'amount' => abs($compensation->value),
            ] : null,
        ]);
    }
}
