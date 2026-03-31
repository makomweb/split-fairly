<?php

namespace App\Controller\API;

use App\Async\Stopwatch;
use App\Instrumentation\InstrumentationHolder;
use App\Invariant\Ensure;
use App\Repository\UserRepository;
use App\SplitFairly\Calculator;
use App\SplitFairly\Compensation;
use App\SplitFairly\Expenses;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api.')]
class CalculateExpensesController extends AbstractController
{
    public function __construct(
        private readonly Calculator $calculator,
        private readonly UserRepository $userRepository,
    ) {
    }

    #[Route('/calculate', name: 'calculate', methods: ['GET'])]
    public function calculate(Request $request): JsonResponse
    {
        $stopwatch = Stopwatch::start();

        $currentUser = $this->getUser();
        if (!$currentUser) {
            return $this->json([
                'error' => 'Please login first!',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $expenses = $this->calculator->calculate();
        Ensure::that(2 === count($expenses), 'Track your expenses first!');

        // Check if specific user requested
        $withUserEmail = $request->query->get('with_user');

        if ($withUserEmail) {
            // Find the specific user to calculate with
            $selectedUser = $this->userRepository->findOneBy(['email' => $withUserEmail]);
            if (!$selectedUser) {
                return $this->json([
                    'error' => sprintf('User %s not found!', $withUserEmail),
                ], Response::HTTP_BAD_REQUEST);
            }

            // Prevent self-calculation
            if ($selectedUser->getEmail() === $currentUser->getUserIdentifier()) {
                return $this->json([
                    'error' => 'Cannot settle up with yourself!',
                ], Response::HTTP_BAD_REQUEST);
            }

            // Filter expenses to only the current user and selected user
            $currentUserExpenses = null;
            $selectedUserExpenses = null;

            foreach ($expenses as $expense) {
                if ($expense->userEmail === $currentUser->getUserIdentifier()) {
                    $currentUserExpenses = $expense;
                } elseif ($expense->userEmail === $selectedUser->getEmail()) {
                    $selectedUserExpenses = $expense;
                }
            }

            if (!$currentUserExpenses || !$selectedUserExpenses) {
                return $this->json([
                    'error' => 'Could not find expenses for selected users',
                ], Response::HTTP_BAD_REQUEST);
            }

            $expenses = [$currentUserExpenses, $selectedUserExpenses];
        }

        $compensation = Compensation::calculate($expenses[0], $expenses[1]);

        InstrumentationHolder::getMetrics()
            ->record('calculate_compensation', $stopwatch->getMillisecondsElapsed(), 'ms');

        InstrumentationHolder::getLogging()
            ->info(sprintf('Calculated: %s (withUser: %s)', $compensation, $withUserEmail ?? 'all'));

        $data = [
            'users' => array_map(
                static fn (Expenses $e) => [
                    'user_email' => $e->userEmail,
                    'categories' => $e->categories(),
                ],
                $expenses
            ),
            'compensation' => $compensation,
        ];

        $json = json_encode($data);
        assert(is_string($json));
        return $this->json([...$data, 'id' => hash('sha256', $json)]);
    }
}
