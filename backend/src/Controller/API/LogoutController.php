<?php

namespace App\Controller\API;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/api', name: 'api.')]
class LogoutController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(Request $request, SessionInterface $session, TokenStorageInterface $tokenStorage): Response
    {
        $this->logger->debug('Entering '.__METHOD__);

        $session->invalidate();
        $tokenStorage->setToken(null);

        $response = new Response(content: null, status: Response::HTTP_NO_CONTENT);

        $response->headers->clearCookie('PHPSESSID', '/', null, false, false, 'lax');
        $response->headers->clearCookie('REMEMBERME', '/', null, false, false, 'lax');

        return $response;
    }
}
