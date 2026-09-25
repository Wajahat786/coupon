<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Singleton PDO connection with prepared statements ONLY.
 * Never interpolate user input into SQL anywhere in this app.
 */
final class Database
{
    /** @var PDO|null */
    private static $pdo = null;

    private function __construct() {}
    private function __clone() {}

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            /** @var array $cfg */
            $cfg = APP_CONFIG['db'];

            /* Fail fast with an actionable message instead of silently
             * connecting as root@127.0.0.1 with no password (shared hosts
             * like Hostinger reject that -> confusing 500 + Access denied). */
            $envFile = $GLOBALS['ENV_LOADED_FROM'] ?? null;
            if ($envFile === null) {
                $msg = 'No .env file found. Looked in: ' . BASE_PATH . '/, '
                     . dirname(BASE_PATH) . '/ and ' . BASE_PATH . '/public/. '
                     . 'Copy .env.example to .env and fill in your Hostinger '
                     . 'MySQL credentials (hPanel -> Databases -> MySQL accounts).';
                error_log('[DB] CONFIG ERROR: ' . $msg);
                throw new RuntimeException($msg);
            }
            if ($cfg['user'] === '' || $cfg['name'] === '') {
                $msg = sprintf(
                    '.env was loaded from %s but DB_NAME/DB_USER are empty. '
                  . 'Fill them with the exact values from hPanel -> MySQL.',
                    $envFile
                );
                error_log('[DB] CONFIG ERROR: ' . $msg);
                throw new RuntimeException($msg);
            }
            if ($cfg['pass'] === '' && $cfg['user'] !== 'root') {
                error_log(sprintf(
                    '[DB] WARNING: DB_PASS is empty in %s while DB_USER=%s. '
                  . 'Hostinger MySQL users always need a password.',
                    $envFile, $cfg['user']
                ));
            }

            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $cfg['host'],
                $cfg['port'],
                $cfg['name'],
                $cfg['charset']
            );

            try {
                self::$pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false, // real server-side prepares
                    PDO::ATTR_STRINGIFY_FETCHES  => false,
                ]);
            } catch (PDOException $e) {
                // Do not leak credentials / host info to output.
                error_log(sprintf(
                    '[DB] connection failed for user "%s" to db "%s" on %s:%d '
                  . '(config source: %s) — %s',
                    $cfg['user'], $cfg['name'], $cfg['host'], $cfg['port'],
                    (string) $envFile, $e->getMessage()
                ));
                throw new RuntimeException('Database connection unavailable.');
            }
        }

        return self::$pdo;
    }

    /** Convenience: run a prepared statement and return the stmt. */
    public static function run(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::pdo()->prepare($sql);
        foreach ($params as $k => $v) {
            $key = is_int($k) ? $k + 1 : $k;
            // match(true) needs PHP 8.0 — keep if/else so this also runs on PHP 7.4
            if (is_int($v)) {
                $type = PDO::PARAM_INT;
            } elseif (is_bool($v)) {
                $type = PDO::PARAM_BOOL;
            } elseif (is_null($v)) {
                $type = PDO::PARAM_NULL;
            } else {
                $type = PDO::PARAM_STR;
            }
            $stmt->bindValue($key, $v, $type);
        }
        $stmt->execute();
        return $stmt;
    }

    public static function lastInsertId(): int
    {
        return (int) self::pdo()->lastInsertId();
    }
}
