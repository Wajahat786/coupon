<?php

declare(strict_types=1);

/**
 * Browser-based admin creator (temporary file).
 *
 * 1) Copy THIS file into public_html/ (web root).
 * 2) Edit the three values below (name, email, password).
 * 3) Open https://yourdomain.com/create_admin_web.php in your browser.
 * 4) DELETE this file immediately after it says "DONE".
 *
 * Security notes:
 *  - Password is hashed with bcrypt cost 12 (same as the app uses).
 *  - If the email already exists, nothing is overwritten unless $RESET_EXISTING = true.
 *  - No output of secrets; generic errors only.
 */

// ===================== EDIT THESE 3 LINES ONLY =====================
$ADMIN_NAME     = 'Zemmart Admin';                 // display name
$ADMIN_EMAIL    = 'you@example.com';               // login email
$ADMIN_PASSWORD = 'ChangeMe-Strong-123';           // min 12 chars, keep quotes
$RESET_EXISTING = false;                           // set true to reset password if email exists
// ===================================================================

if ($ADMIN_NAME === 'Zemmart Admin' || $ADMIN_EMAIL === 'you@example.com' || $ADMIN_PASSWORD === 'ChangeMe-Strong-123') {
    http_response_code(400);
    die('ERROR: Pehle file ke upar wale 3 values (name/email/password) edit karein, phir ye page refresh karein.');
}

if (strlen($ADMIN_PASSWORD) < 12) {
    http_response_code(400);
    die('ERROR: Password kam az kam 12 characters ka ho.');
}

// Locate bootstrap: works whether this file sits in /database or in public_html
$candidates = [
    __DIR__ . '/../app/bootstrap.php',        // inside database/ folder
    __DIR__ . '/coupon.zemmart.com/app/bootstrap.php', // rare fallback
];
$bootstrap = null;
foreach ($candidates as $c) {
    if (is_file($c)) { $bootstrap = $c; break; }
}
if ($bootstrap === null) {
    http_response_code(500);
    die('ERROR: app/bootstrap.php nahi mila. Is file ko public_html/ ke andar copy karein.');
}
require $bootstrap;
if (is_file(dirname($bootstrap) . '/helpers.php')) {
    require dirname($bootstrap) . '/helpers.php';
}

use App\Core\Database;

$email = strtolower(trim($ADMIN_EMAIL));
$hash  = password_hash($ADMIN_PASSWORD, PASSWORD_BCRYPT, ['cost' => 12]);

try {
    $existing = Database::run('SELECT id, role FROM users WHERE email = :e LIMIT 1', [':e' => $email])->fetch();

    if ($existing) {
        if (!$RESET_EXISTING) {
            die('User with this email ALREADY exists. Agar password reset karna hai to $RESET_EXISTING = true karein aur dobara run karein.');
        }
        Database::run('UPDATE users SET password_hash = :p, role = :r, is_active = 1 WHERE id = :id', [
            ':p' => $hash, ':r' => 'admin', ':id' => (int)$existing['id'],
        ]);
        echo "Password RESET + admin role set for {$email}. ID: {$existing['id']}\n";
    } else {
        Database::run(
            'INSERT INTO users (name, email, password_hash, role, is_active, created_at)
             VALUES (:n, :e, :p, :r, 1, NOW())',
            [
                ':n' => mb_substr(trim($ADMIN_NAME), 0, 100),
                ':e' => $email,
                ':p' => $hash,
                ':r' => 'admin',
            ]
        );
        echo "Admin CREATED. ID: " . Database::lastInsertId() . " | Login: {$email}\n";
    }
    echo "\nDONE ✅ — AB IS FILE KO TURANT DELETE KARO (public_html se).\n";
    echo "Phir yahan login karo: /admin/login\n";
} catch (Throwable $e) {
    http_response_code(500);
    error_log('[create_admin_web] ' . $e->getMessage());
    die('ERROR: Kuch galat hua. storage/logs/php-error.log check karein.');
}
