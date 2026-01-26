<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class SpaController
{
    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly Environment $twig
    ) {}

    #[Route('/', name: 'app_home')]
    #[Route('/{path}', name: 'app_spa', requirements: ['path' => '.*'], priority: -10)]
    public function index(Request $request): Response
    {
        $buildIndex = $this->kernel->getProjectDir() . '/public/build/index.html';
        if ($this->kernel->getEnvironment() === 'prod' && file_exists($buildIndex)) {
            return new Response(
                file_get_contents($buildIndex),
                Response::HTTP_OK,
                ['Content-Type' => 'text/html; charset=utf-8']
            );
        }

        // In dev, render the HTML shell using Twig
        $viteUrl = sprintf(
            '%s://%s:5173',
            $request->isSecure() ? 'https' : 'http',
            $request->getHost()
        );
        $html = $this->twig->render('spa.html.twig', [
            'vite' => true,
            'vite_url' => $viteUrl,
        ]);
        return new Response($html, Response::HTTP_OK, ['Content-Type' => 'text/html; charset=utf-8']);
    }
}

