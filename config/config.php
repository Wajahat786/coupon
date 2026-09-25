<?php

declare(strict_types=1);

/**
 * Central configuration. Values come from environment variables so that
 * secrets NEVER live in this file. See .env.example
 */
return [
    'app' => [
        'name'     => getenv('APP_NAME') ?: 'DealHub',
        'env'      => getenv('APP_ENV') ?: 'production',
        'debug'    => filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN),
        'url'      => rtrim(getenv('APP_URL') ?: '', '/'),
        'timezone' => getenv('APP_TZ') ?: 'UTC',
    ],

    'db' => [
        'host'     => getenv('DB_HOST') ?: '127.0.0.1',
        'port'     => (int) (getenv('DB_PORT') ?: 3306),
        'name'     => getenv('DB_NAME') ?: 'dealhub',
        'user'     => getenv('DB_USER') ?: 'root',
        'pass'     => getenv('DB_PASS') ?: '',
        'charset'  => 'utf8mb4',
    ],

    'security' => [
        // Name of the session cookie
        'cookie_name'  => 'dealhub_session',
        'cookie_secure' => filter_var(getenv('COOKIE_SECURE') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        // Max attempts per IP+identifier before lockout
        'login_max_attempts' => 5,
        'login_lockout_seconds' => 900, // 15 minutes
    ],
];
