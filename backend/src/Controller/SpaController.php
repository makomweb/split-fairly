<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SpaController
{
    #[Route('/', name: 'app_home')]
    #[Route('/{path}', name: 'app_spa', requirements: ['path' => '.*'], priority: -10)]
    public function index(Request $request): Response
    {
        // If a built frontend exists, serve the built index.html
        $buildIndex = dirname(__DIR__, 3) . '/public/build/index.html';
        if (file_exists($buildIndex)) {
            return new Response(
                file_get_contents($buildIndex),
                Response::HTTP_OK,
                ['Content-Type' => 'text/html; charset=utf-8']
            );
        }

        // In development, serve an HTML shell that loads the App from Vite (/src/main.tsx)
        $viteUrl = sprintf(
            '%s://%s:5173',
            $request->isSecure() ? 'https' : 'http',
            $request->getHost()
        );

        $html = <<<HTML
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Fair Split App</title>
  </head>
  <body>
    <div id="root"></div>
    <script type="module" src="{$viteUrl}/@vite/client"></script>
    <script type="module" src="{$viteUrl}/src/main.tsx"></script>
  </body>
</html>
HTML;

        return new Response($html, Response::HTTP_OK, ['Content-Type' => 'text/html; charset=utf-8']);
    }
}
