<?php

declare(strict_types=1);

namespace App\Async;

final class Stopwatch
{
    private function __construct(
        private readonly float $started,
        private ?float $stopped = null,
    ) {
    }

    public static function start(): self
    {
        return self::from(microtime(true));
    }

    public static function from(float $started): self
    {
        $obj = new self($started);
        $obj->stop();

        return $obj;
    }

    public function stop(): void
    {
        assert(is_null($this->stopped), 'Stopwatch can only be stopped once.');

        $this->stopped = microtime(true);
    }

    public function getMillisecondsElapsed(): float
    {
        return floor($this->getSecondsElapsed() * 1000);
    }

    public function getSecondsElapsed(): float
    {
        return microtime(true) - $this->started;
    }
}
