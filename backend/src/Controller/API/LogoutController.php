<?php

namespace App\Controller\API;

use App\Instrumentation\InstrumentationHolder;
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
        $tracer = InstrumentationHolder::getTracing()->createTracer(__METHOD__, __FILE__);

        // Invalidate the session
        $session->invalidate();
        $tokenStorage->setToken(null);

        $response = new Response(content: null, status: Response::HTTP_NO_CONTENT);

        // Clear both session and remember-me cookies
        $response->headers->clearCookie('PHPSESSID', '/', null, false, false, 'lax');
        $response->headers->clearCookie('REMEMBERME', '/', null, false, false, 'lax');
        $response->headers->clearCookie('ADMIN_REMEMBERME', '/admin', null, false, false, 'lax');

        return $response;
    }
}
