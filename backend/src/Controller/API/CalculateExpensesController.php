<?php

namespace App\Controller\API;

use App\Async\Stopwatch;
use App\Instrumentation\InstrumentationHolder;
use App\Invariant\Ensure;
use App\SplitFairly\Calculator;
use App\SplitFairly\Compensation;
use App\SplitFairly\Expenses;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api.')]
class CalculateExpensesController extends AbstractController
{
    public function __construct(
        private readonly Calculator $calculator,
    ) {
    }

    #[Route('/calculate', name: 'calculate', methods: ['GET'])]
    public function calculate(): JsonResponse
    {
        $stopwatch = Stopwatch::start();

        $expenses = $this->calculator->calculate();

        Ensure::that(2 === count($expenses));

        $compensation = Compensation::calculate($expenses[0], $expenses[1]);

        InstrumentationHolder::getMetrics()
            ->record('calculate_compensation', $stopwatch->getMillisecondsElapsed(), 'ms');

        InstrumentationHolder::getLogging()
            ->info(sprintf('Calculated: %s', $compensation));

        return $this->json([
            'users' => array_map(
                static fn (Expenses $e) => [
                    'user_email' => $e->userEmail,
                    'categories' => $e->categories(),
                ],
                $expenses
            ),
            'compensation' => $compensation,
        ]);
    }
}
