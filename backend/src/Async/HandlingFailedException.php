<?php

declare(strict_types=1);

namespace App\Async;

final class HandlingFailedException extends \Exception
{
    public function __construct(\Throwable $ex)
    {
        parent::__construct($ex->getMessage(), previous: $ex);
    }
}
