<?php

declare(strict_types=1);

namespace App\Instrumentation\Null;

use App\Instrumentation\LoggingInterface;

final readonly class Logging implements LoggingInterface
{
    public function info(string|\Stringable $message): void
    {
    }

    public function debug(string|\Stringable $message): void
    {
    }

    public function exception(\Throwable $ex): void
    {
    }
}
