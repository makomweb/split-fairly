<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SpaController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // If a built frontend exists, serve the built index.html
        $buildIndex = dirname(__DIR__, 2) . '/public/build/index.html';
        if (file_exists($buildIndex)) {
            return new Response(file_get_contents($buildIndex));
        }

        // In development, redirect to the Vite dev server
        $devUrl = 'http://localhost:5173/';
        return new RedirectResponse($devUrl);
    }
}
