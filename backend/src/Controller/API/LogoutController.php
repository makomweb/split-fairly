<?php

namespace App\Controller\API;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/api', name: 'api.')]
class LogoutController extends AbstractController
{
    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(Request $request, SessionInterface $session, TokenStorageInterface $tokenStorage): Response
    {
        $session->invalidate();
        $tokenStorage->setToken(null);

        $response = new Response(content: null, status: Response::HTTP_NO_CONTENT);

        $response->headers->clearCookie('PHPSESSID', '/', null, false, false, 'lax');
        $response->headers->clearCookie('REMEMBERME', '/', null, false, false, 'lax');

        return $response;
    }
}
