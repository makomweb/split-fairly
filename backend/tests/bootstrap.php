<?php

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
