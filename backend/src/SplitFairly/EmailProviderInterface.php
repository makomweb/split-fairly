<?php

declare(strict_types=1);

namespace App\SplitFairly;

interface EmailProviderInterface
{
    public function getEmailFor(string $userUuid): string;
}
