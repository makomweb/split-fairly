<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/.env')) {
    new Dotenv()->bootEnv(dirname(__DIR__).'/.env');
}

if (file_exists(dirname(__DIR__).'/.env.test')) {
    new Dotenv()->bootEnv(dirname(__DIR__).'/.env.test');
}

// Ensure test environment variables are set
if (isset($_SERVER['APP_ENV']) && 'test' === $_SERVER['APP_ENV']) {
    $_ENV['APP_ENV'] = 'test';
}
