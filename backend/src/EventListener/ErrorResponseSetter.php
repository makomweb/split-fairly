<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

#[AsEventListener]
final readonly class ErrorResponseSetter
{
    public function __construct(
        #[Autowire('%kernel.debug%')]
        private bool $isDebug,
    ) {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $data = [
            'type' => self::getType($exception),
            'title' => 'An error occurred',
            'status' => self::getCode($exception),
            'detail' => $exception->getMessage(),
            'class' => get_class($exception),
        ];

        if ($this->isDebug) {
            $data['trace'] = $exception->getTrace();
        }

        $event->setResponse(
            new JsonResponse(
                $data,
                self::getCode($exception)
            )
        );
    }

    private static function getCode(\Throwable $exception): int
    {
        return 0 !== $exception->getCode() && 600 > $exception->getCode()
            ? $exception->getCode()
            : Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    private static function getType(\Throwable $exception): string
    {
        $code = self::getCode($exception);

        return match (true) {
            $code >= 500 => 'https://tools.ietf.org/html/rfc2616#section-10.5',
            $code >= 400 => 'https://tools.ietf.org/html/rfc2616#section-10.4',
            $code >= 300 => 'https://tools.ietf.org/html/rfc2616#section-10.3',
            $code >= 200 => 'https://tools.ietf.org/html/rfc2616#section-10.2',
            default => 'https://tools.ietf.org/html/rfc2616#section-10',
        };
    }
}
