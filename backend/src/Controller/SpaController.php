<?php

namespace App\Controller;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class SpaController
{
    public function __construct(
        #[Autowire('%kernel.environment%')]
        private string $environment,

        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
    ) {
    }

    #[Route('/{path}', name: 'app_spa', requirements: ['path' => '.*'], priority: -10)]
    public function index(): Response
    {
        // Development: serve via Vite with Hot Module Reload
        if ('dev' === $this->environment) {
            return new RedirectResponse('http://localhost:5173');
        }

        // Production: serve built HTML
        $buildIndex = $this->projectDir.'/public/build/index.html';
        if (!file_exists($buildIndex)) {
            throw new \RuntimeException("SPA build not found at {$buildIndex}. Run make npm-build!");
        }

        $content = file_get_contents($buildIndex);
        assert(is_string($content));

        return new Response(
            $content,
            Response::HTTP_OK,
            ['Content-Type' => 'text/html; charset=utf-8']
        );
    }
}
