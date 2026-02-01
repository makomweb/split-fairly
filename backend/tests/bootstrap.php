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

// Initialize test database schema
if ('test' === ($_SERVER['APP_ENV'] ?? 'dev')) {
    $databaseUrl = isset($_SERVER['DATABASE_URL']) && is_string($_SERVER['DATABASE_URL']) ? $_SERVER['DATABASE_URL'] : '';
    $isInMemoryDb = str_contains($databaseUrl, ':memory:');

    if ($isInMemoryDb) {
        // For in-memory SQLite, we need to create schema using Doctrine directly
        // This avoids kernel shutdown which would destroy the in-memory database
        $kernel = new App\Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
        $kernel->boot();

        $container = $kernel->getContainer();
        $em = $container->get('doctrine.orm.entity_manager');

        try {
            if (!$em instanceof \Doctrine\ORM\EntityManagerInterface) {
                throw new Exception('Entity manager not found or invalid type');
            }

            $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($em);
            $metadata = $em->getMetadataFactory()->getAllMetadata();
            $schemaTool->createSchema($metadata);
        } catch (Exception $e) {
            error_log('Database schema creation warning: '.$e->getMessage());
        }
    } else {
        // For other databases, run migrations via console
        $kernel = new App\Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
        $application = new Application($kernel);
        $application->setAutoExit(false);

        try {
            $input = new ArrayInput(['command' => 'doctrine:database:create', '--if-not-exists' => true]);
            $application->run($input);
            $input = new ArrayInput(['command' => 'doctrine:migrations:migrate', '--no-interaction' => true, '--allow-no-migration' => true]);
            $application->run($input);
        } catch (Exception $e) {
            error_log('Database initialization warning: '.$e->getMessage());
        }

        $kernel->shutdown();
    }
}
