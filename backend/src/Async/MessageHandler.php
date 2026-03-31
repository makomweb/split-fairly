<?php

declare(strict_types=1);

namespace App\Async;

use App\Instrumentation\Instrumentation;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class MessageHandler
{
    public function __construct(private Instrumentation $instrumentation)
    {
    }

    public function __invoke(Message $message): void
    {
        $this->tryHandle($message);
    }

    private function tryHandle(Message $message): void
    {
        $tracer = $this->instrumentation->getTracing()->createTracer(__METHOD__, __FILE__);

        try {
            $this->handle($message);
        } catch (\Throwable $throwable) {
            $tracer->recordException('Handling the message has failed', $throwable);
        } finally {
            $elapsed = Stopwatch::from($message->createdAt)->getMillisecondsElapsed();
            $this->instrumentation->getMetrics()->record('handle_message', $elapsed, 'ms');
        }
    }

    private function handle(Message $message): void
    {
        $this->instrumentation->getLogging()->info(sprintf('📫 Message handled: "%s"', $message->type));
    }
}
