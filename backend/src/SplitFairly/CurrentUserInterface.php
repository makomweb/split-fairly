<?php

declare(strict_types=1);

namespace App\SplitFairly;

interface CurrentUserInterface
{
    public function getUuid(): string;

    public function getEmail(): string;
}
