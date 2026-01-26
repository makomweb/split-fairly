<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SpaController extends AbstractController
{
    public function __construct(
        #[Autowire('%kernel.environment%')]
        private readonly string $environment,
        
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir
    ) {
    }

    #[Route('/{path}', name: 'app_spa', requirements: ['path' => '.*'], priority: -10)]
    public function index(Request $request): Response
    {
        if ($this->environment === 'dev') {
            // Dev mode: serve Vite shell through Symfony (keeps toolbar visible)
            $viteUrl = sprintf(
                '%s://%s:5173',
                $request->isSecure() ? 'https' : 'http',
                $request->getHost()
            );
            
            return $this->render('spa.html.twig', [
                'vite' => true,
                'vite_url' => $viteUrl,
            ], new Response(null, Response::HTTP_OK, [
                'Content-Type' => 'text/html; charset=utf-8',
            ]));
        }

        // Prod mode: serve built static HTML
        $buildIndex = $this->projectDir . '/public/build/index.html';
        if (!file_exists($buildIndex)) {
            throw new \RuntimeException("SPA build not found at {$buildIndex}");
        }

        $content = file_get_contents($buildIndex);
        return $this->render('spa.html.twig', [
            'content' => $content,
        ], new Response(null, Response::HTTP_OK, [
            'Content-Type' => 'text/html; charset=utf-8',
        ]));
    }
}

