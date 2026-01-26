<?php

namespace App\Controller\API;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api', name: 'api.')]
class LoginController extends AbstractController
{
    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return $this->json(
                ['message' => 'missing credentials'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        return $this->json([
            'user' => $user->getUserIdentifier(),
            // TODO: list roles, permissions, created tokens etc.
        ]);
    }
}
