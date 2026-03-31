<?php

namespace App\Controller\API;

use App\Repository\UserRepository;
use App\SplitFairly\Expense;
use App\SplitFairly\ExpenseTracker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api.')]
class TrackExpenseController extends AbstractController
{
    public function __construct(
        private readonly ExpenseTracker $tracker,
        private readonly UserRepository $userRepository,
    ) {
    }

    #[Route('/track', name: 'track', methods: ['POST'])]
    public function track(#[MapRequestPayload] Expense $expense): JsonResponse
    {
        $currentUser = $this->getUser();

        if (!$currentUser instanceof \Symfony\Component\Security\Core\User\UserInterface) {
            return $this->json([
                'error' => 'Please login first!',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Validate Lent expenses: location must be recipient's email and must differ from current user
        if ('Lent' === $expense->type) {
            $currentUserEmail = $currentUser->getUserIdentifier();
            $recipientEmail = $expense->location;

            // Check recipient email is not the same as current user
            if ($recipientEmail === $currentUserEmail) {
                return $this->json([
                    'error' => 'Cannot lend money to yourself!',
                ], Response::HTTP_BAD_REQUEST);
            }

            // Check recipient email exists in the system
            $recipientUser = $this->userRepository->findOneBy(['email' => $recipientEmail]);
            if (!$recipientUser instanceof \App\Entity\User) {
                return $this->json([
                    'error' => sprintf('User %s not found!', $recipientEmail),
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        $this->tracker->track($expense);

        return $this->json($expense);
    }
}
