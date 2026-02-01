<?php

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

// Force test environment for PHPUnit
if (!isset($_ENV['APP_ENV'])) {
    $_ENV['APP_ENV'] = 'dev';
}
if (isset($_SERVER['APP_ENV']) && 'test' === $_SERVER['APP_ENV']) {
    $_ENV['APP_ENV'] = 'test';
    $_SERVER['APP_ENV'] = 'test';
}

(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

// Initialize test database schema once at bootstrap time
if ('test' === ($_SERVER['APP_ENV'] ?? 'dev')) {
    $kernel = new App\Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
    $application = new Application($kernel);
    $application->setAutoExit(false);

    try {
        // Create the database if it doesn't exist
        $input = new ArrayInput(['command' => 'doctrine:database:create', '--if-not-exists' => true]);
        $application->run($input);

        // Run migrations to create tables
        $input = new ArrayInput([
            'command' => 'doctrine:migrations:migrate',
            '--no-interaction' => true,
            '--allow-no-migration' => true,
        ]);
        $application->run($input);
    } catch (Exception $e) {
        error_log('Database initialization warning: '.$e->getMessage());
    }

    $kernel->shutdown();
}
