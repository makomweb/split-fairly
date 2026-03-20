<?php

declare(strict_types=1);

namespace App\Instrumentation\PsrLog;

use Monolog\Formatter\LineFormatter;

class CustomLineFormatter extends LineFormatter
{
    public function __construct()
    {
        $timestampFormat = 'd-M-Y H:i:s'; // Format: 03-Mar-2025 10:27:23
        $format = "[%datetime%] %level_name%: %message%\n";
        parent::__construct($format, $timestampFormat, false, true);
    }
}
