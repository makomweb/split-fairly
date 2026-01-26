<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SpaController extends AbstractController
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir
    ) {
    }

    #[Route('/{path}', name: 'app_spa', requirements: ['path' => '.*'], priority: -10)]
    public function index(): Response
    {
        $buildIndex = $this->projectDir . '/public/build/index.html';
        if (!file_exists($buildIndex)) {
            throw new \RuntimeException("SPA build not found at {$buildIndex}");
        }

        return new Response(
            file_get_contents($buildIndex),
            Response::HTTP_OK,
            ['Content-Type' => 'text/html; charset=utf-8']
        );
    }
}

