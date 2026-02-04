<?php

declare(strict_types=1);

namespace App\Instrumentation;

interface LoggingInterface
{
    public function info(string|\Stringable $message): void;

    public function exception(\Throwable $ex): void;
}
