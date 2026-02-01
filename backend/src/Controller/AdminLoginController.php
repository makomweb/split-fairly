<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AdminLoginController extends AbstractController
{
    public function __construct(private readonly AuthenticationUtils $utils)
    {
    }

    #[Route(path: '/admin/login', name: 'admin.login')]
    public function login(): Response
    {
        // get the login error if there is one
        $error = $this->utils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $this->utils->getLastUsername();

        return $this->render('admin_login/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/admin/login_check', name: 'admin.login_check')]
    public function loginCheck(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the login key on your firewall.');
    }

    #[Route(path: '/admin/logout', name: 'admin.logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
