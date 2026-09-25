<?php

declare(strict_types=1);

/**
 * CLI seed script: php database/seed_admin.php "Name" "email@x.com" "StrongPass!"
 * Creates (or resets) an admin account. Run on the server only.
 */
if (PHP_SAPI !== 'cli') {
    exit("CLI only\n");
}

require __DIR__ . '/../app/bootstrap.php';
if (is_file(__DIR__ . '/../app/helpers.php')) {
    require __DIR__ . '/../app/helpers.php'; // mb_* polyfills for CLI too
}

use App\Models\User;

[$script, $name, $email, $password] = array_pad($argv ?? [], 4, null);

if (!$name || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$password || strlen($password) < 10) {
    exit("Usage: php database/seed_admin.php \"Name\" \"admin@example.com\" \"password-min-10-chars\"\n");
}

if (User::findByEmail($email)) {
    fwrite(STDERR, "User exists — refusing to overwrite. Delete manually if you really mean it.\n");
    exit(1);
}

$id = User::create($name, $email, $password, 'admin');
echo "Admin created with id $id ($email)\n";
