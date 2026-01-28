<?php

declare(strict_types=1);

namespace App\Async;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class MessageHandler
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(Message $message): void
    {
        $this->tryHandle($message);
    }

    private function tryHandle(Message $message): void
    {
        $this->logger->debug('Entering '.__METHOD__);

        try {
            $this->handle($message);
        } catch (\Throwable $ex) {
            $this->logger->error(sprintf('💥 Handling failed due to %s', $ex->getMessage()));
        } finally {
            $elapsed = Stopwatch::from($message->createdAt)->getMillisecondsElapsed();
            $this->logger->info(sprintf('⌚ Handling message took: %.0f ms', $elapsed));
        }
    }

    private function handle(Message $message): void
    {
        $this->logger->info('📫 Handle message: '.$message->type);
    }
}
