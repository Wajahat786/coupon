<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Security;
use App\Models\Setting;

/** Shorthand escape. */
function e(mixed $v): string
{
    return Security::e($v);
}

/** Current authenticated user (or null). */
function auth_user(): ?array
{
    return Auth::user();
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(Security::csrfToken()) . '">';
}

/** Old-input flash for forms. */
function flash_set(string $key, $val): void
{
    $_SESSION['_flash'][$key] = $val;
}

function flash_get(string $key, $default = '')
{
    $v = $_SESSION['_flash'][$key] ?? $default;
    unset($_SESSION['_flash'][$key]);
    return $v;
}

function flash_message(): ?array
{
    $m = $_SESSION['_flash_msg'] ?? null;
    unset($_SESSION['_flash_msg']);
    return $m; // ['type'=>'success|error','text'=>...]
}

function flash_message_set(string $type, string $text): void
{
    $_SESSION['_flash_msg'] = ['type' => $type, 'text' => $text];
}

/** Open-redirect-safe local path. */
function safe_next(?string $url, string $fallback = '/'): string
{
    if (!is_string($url) || $url === '' || !str_starts_with($url, '/')
        || str_starts_with($url, '//') || str_contains($url, "\\") || preg_match('/^\/\d/', $url)) {
        return $fallback;
    }
    return $url;
}

function setting(string $key, string $default = ''): string
{
    return Setting::get($key, $default);
}

function time_ago(?string $dt): string
{
    if (!$dt) return '';
    $t = strtotime($dt);
    $d = time() - $t;
    if ($d < 60)    return 'just now';
    if ($d < 3600)  return floor($d / 60) . 'm ago';
    if ($d < 86400) return floor($d / 3600) . 'h ago';
    if ($d < 2592000) return floor($d / 86400) . 'd ago';
    return date('M j, Y', $t);
}

function expires_label(?string $date): array
{
    if (!$date) return ['Never expires', ''];
    $days = (int)((strtotime($date . ' 23:59:59') - time()) / 86400);
    if ($days < 0)  return ['Expired', 'danger'];
    if ($days <= 7) return ["Expires in $days day" . ($days === 1 ? '' : 's'), 'warn'];
    return ['Expires ' . date('M j, Y', strtotime($date)), ''];
}
