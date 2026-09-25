<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class User
{
    public static function find(int $id): ?array
    {
        return Database::run('SELECT * FROM users WHERE id = :id', [':id' => $id])->fetch() ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        return Database::run(
            'SELECT * FROM users WHERE email = :e LIMIT 1',
            [':e' => strtolower(trim($email))]
        )->fetch() ?: null;
    }

    public static function create(string $name, string $email, string $password, string $role = 'submitter'): int
    {
        Database::run(
            'INSERT INTO users (name, email, password_hash, role, is_active, created_at)
             VALUES (:n, :e, :p, :r, 1, NOW())',
            [
                ':n' => mb_substr(trim($name), 0, 100),
                ':e' => strtolower(trim($email)),
                ':p' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
                ':r' => in_array($role, ['admin', 'submitter'], true) ? $role : 'submitter',
            ]
        );
        return Database::lastInsertId();
    }

    public static function touchLogin(int $id, string $ip): void
    {
        Database::run(
            'UPDATE users SET last_login_at = NOW(), last_login_ip = :ip WHERE id = :id',
            [':id' => $id, ':ip' => substr($ip, 0, 45)]
        );
    }

    public static function all(): array
    {
        return Database::run('SELECT id,name,email,role,is_active,created_at,last_login_at FROM users ORDER BY id DESC')->fetchAll();
    }

    public static function setActive(int $id, bool $active): void
    {
        Database::run('UPDATE users SET is_active = :a WHERE id = :id', [':a' => $active ? 1 : 0, ':id' => $id]);
    }

    public static function setPassword(int $id, string $plain): void
    {
        Database::run(
            'UPDATE users SET password_hash = :p WHERE id = :id',
            [':p' => password_hash($plain, PASSWORD_BCRYPT, ['cost' => 12]), ':id' => $id]
        );
    }
}
