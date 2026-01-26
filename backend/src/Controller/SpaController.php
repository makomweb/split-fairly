<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpClient\HttpClient;
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
    public function index(): Response
    {
        if ($this->environment === 'dev') {
            // Dev mode: fetch from Vite dev server and inject toolbar
            // Use Docker network hostname (npm-dev service) from within container
            $viteUrl = 'http://npm-dev:5173/';
            
            $httpClient = HttpClient::create();
            try {
                $response = $httpClient->request('GET', $viteUrl);
                $content = $response->getContent();
            } catch (\Exception $e) {
                throw new \RuntimeException("Vite dev server not available at {$viteUrl}: " . $e->getMessage());
            }

            return new Response(
                $content,
                Response::HTTP_OK,
                ['Content-Type' => 'text/html; charset=utf-8']
            );
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

