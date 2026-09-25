<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Security;
use App\Core\View;
use App\Models\Audit;
use App\Models\Setting;
use App\Models\User;

/**
 * Site settings, user management and audit log. Admin-only.
 */
final class SettingsController
{
    private const ALLOWED_KEYS = ['site_name', 'tagline', 'default_theme'];

    public function index(): void
    {
        Auth::requireLogin(true);
        View::render('admin/settings', [
            'pageTitle' => 'Site Settings',
            'settings'  => array_column(Setting::all(), 's_value', 's_key'),
        ], 'layouts/admin');
    }

    public function save(): void
    {
        Auth::requireLogin(true);
        Security::verifyCsrf();

        foreach (self::ALLOWED_KEYS as $key) {          // strict allow-list of keys
        if (isset($_POST[$key])) {
            Setting::set($key, mb_substr(trim((string) $_POST[$key]), 0, 255));
        }
        }
        Audit::log(Auth::id(), 'settings.update', implode(',', self::ALLOWED_KEYS));
        flash_message_set('success', 'Settings saved.');
        header('Location: /admin/settings');
    }

    /* ---------------- Users ---------------- */

    public function users(): void
    {
        Auth::requireLogin(true);
        View::render('admin/users', [
            'pageTitle' => 'Users',
            'users'     => User::all(),
        ], 'layouts/admin');
    }

    public function addUser(): void
    {
        Auth::requireLogin(true);
        Security::verifyCsrf();

        $name     = mb_substr(trim((string) ($_POST['name'] ?? '')), 0, 100);
        $email    = mb_substr(trim((string) ($_POST['email'] ?? '')), 0, 190);
        $password = (string) ($_POST['password'] ?? '');
        $role     = in_array($_POST['role'] ?? '', ['admin', 'submitter'], true) ? $_POST['role'] : 'submitter';

        if (mb_strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 10) {
            flash_message_set('error', 'Valid name, email and a 10+ char password are required.');
        } elseif (User::findByEmail($email)) {
            flash_message_set('error', 'Email already in use.');
        } else {
            $id = User::create($name, $email, $password, $role);
            Audit::log(Auth::id(), 'user.create', "user#$id ($role)");
            flash_message_set('success', "User #$id created.");
        }
        header('Location: /admin/users');
    }

    public function toggleUser(): void
    {
        Auth::requireLogin(true);
        Security::verifyCsrf();

        $id = (int) ($_POST['id'] ?? 0);
        $me = Auth::id();
        $target = User::find($id);

        if (!$target || $id === $me) {
            flash_message_set('error', 'You cannot disable yourself (or that user does not exist).');
        } else {
            // Never let the LAST active admin be disabled.
            if ((int) $target['is_active'] === 1 && $target['role'] === 'admin') {
                $admins = (int) Database::run("SELECT COUNT(*) c FROM users WHERE role='admin' AND is_active=1")->fetch()['c'];
                if ($admins <= 1) {
                    flash_message_set('error', 'Cannot disable the last active admin.');
                    header('Location: /admin/users');
                    return;
                }
            }
            User::setActive($id, (int) $target['is_active'] !== 1);
            Audit::log($me, 'user.toggle', "user#$id => " . ((int) $target['is_active'] !== 1 ? 'active' : 'disabled'));
            flash_message_set('success', 'User status updated.');
        }
        header('Location: /admin/users');
    }

    /* ---------------- Audit log ---------------- */

    public function audit(): void
    {
        Auth::requireLogin(true);
        View::render('admin/audit', [
            'pageTitle' => 'Audit Log',
            'logs'      => Audit::recent(200),
        ], 'layouts/admin');
    }
}
