<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Security;
use App\Models\Setting;

/* ---------------------------------------------------------------------------
 * mbstring polyfills — shared hosting often ships PHP WITHOUT ext-mbstring.
 * Declared in the global namespace so calls resolve even from namespaced code.
 * ------------------------------------------------------------------------- */
if (!function_exists('mb_substr')) {
    function mb_substr(string $s, int $start, ?int $length = null, ?string $enc = null): string
    {
        if ($enc !== null && strcasecmp($enc, 'UTF-8') !== 0) {
            return substr($s, $start, $length ?? PHP_INT_MAX);
        }
        // Grapheme-ish safe split on UTF-8 code points
        $chars = preg_split('//u', $s, -1, PREG_SPLIT_NO_EMPTY) ?: str_split($s);
        return implode('', array_slice($chars, $start, $length));
    }
}
if (!function_exists('mb_strlen')) {
    function mb_strlen(string $s, ?string $enc = null): int
    {
        if ($enc !== null && strcasecmp($enc, 'UTF-8') !== 0) {
            return strlen($s);
        }
        return count(preg_split('//u', $s, -1, PREG_SPLIT_NO_EMPTY) ?: str_split($s));
    }
}
if (!function_exists('mb_strtolower')) {
    function mb_strtolower(string $s, ?string $enc = null): string
    {
        return strtolower($s);
    }
}

/** Shorthand escape. */
function e($v): string
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
