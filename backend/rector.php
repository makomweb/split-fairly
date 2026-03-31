<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Symfony\Set\SymfonySetList;
use Rector\Set\ValueObject\SetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    // Parallel processing (faster)
    ->withParallel()
    // Target PHP 8.5
    ->withPhpSets(php85: true)
    ->withSets([
        // Symfony 8.0 rules
        SymfonySetList::SYMFONY_80,
        // Code quality
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
    ]);
