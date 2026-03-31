<?php

namespace App\Controller\API;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api.')]
class ListUsersController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    #[Route('/users', name: 'list_users', methods: ['GET'])]
    public function listUsers(): JsonResponse
    {
        $currentUser = $this->getUser();

        if (!$currentUser instanceof \Symfony\Component\Security\Core\User\UserInterface) {
            return $this->json([
                'error' => 'Please login first!',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Provide only other non-admin users:
        $users = array_map(
            static fn (User $user) => [
                'id' => $user->getUuid()->toRfc4122(),
                'email' => $user->getEmail(),
            ],
            array_filter(
                $this->userRepository->findAll(),
                static fn (User $user) => !$user->isAdmin()
                    && $user->getUserIdentifier() !== $currentUser->getUserIdentifier()
            )
        );

        return $this->json(['users' => array_values($users)]);
    }
}
