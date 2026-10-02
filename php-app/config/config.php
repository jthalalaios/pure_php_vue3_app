<?php
declare(strict_types=1);

return [
    'app' => [
        'env'  => getenv('APP_ENV') ?: 'development',
        'name' => 'Pure PHP App',
    ],

    'db' => [
        'host'     => getenv('DB_HOST')     ?: 'postgres-18.3-alpine',
        'port'     => getenv('DB_PORT')     ?: '5432',
        'database' => getenv('DB_DATABASE') ?: 'pure_php_app',
        'username' => getenv('DB_USERNAME') ?: 'php_app_user',
        'password' => getenv('DB_PASSWORD') ?: '',
    ],

    'redis' => [
        'host' => getenv('REDIS_HOST') ?: 'redis-alpine',
        'port' => (int) (getenv('REDIS_PORT') ?: 6379),
    ],
];
