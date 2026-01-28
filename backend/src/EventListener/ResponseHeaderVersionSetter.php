<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

#[AsEventListener]
final readonly class ResponseHeaderVersionSetter
{
    public function __construct(
        #[Autowire('%app.version%')]
        private string $version,
    ) {
    }

    public function __invoke(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $response->headers->set('X-Version', $this->version);
    }
}
