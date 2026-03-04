<?php

namespace App\Controller\API;

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

        if (!$currentUser) {
            return $this->json([
                'error' => 'Please login first!',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Get all users except the current user
        $allUsers = $this->userRepository->findAll();
        $otherUsers = array_filter($allUsers, fn ($user) => $user->getUserIdentifier() !== $currentUser->getUserIdentifier());

        $users = array_map(fn ($user) => [
            'id' => $user->getUuid()->toRfc4122(),
            'email' => $user->getEmail(),
        ], $otherUsers);

        return $this->json(['users' => array_values($users)]);
    }
}
