<?php

declare(strict_types=1);

/**
 * Front controller — ALL traffic is routed through here (see public/.htaccess).
 * Tiny explicit router: no dynamic file inclusion based on user input.
 */

$bootstrap = is_file(__DIR__ . '/../app/bootstrap.php')
    ? __DIR__ . '/../app/bootstrap.php'   // layout A: public/ is the web root
    : __DIR__ . '/app/bootstrap.php';     // layout B: everything in public_html
require $bootstrap;
require dirname($bootstrap) . '/helpers.php';
require BASE_PATH . '/views/partials/icons.php';

use App\Controllers\Admin\CouponController as AdminCoupon;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\ReferralController as AdminReferral;
use App\Controllers\Admin\SettingsController;
use App\Controllers\Web\AuthController;
use App\Controllers\Web\DealController;
use App\Controllers\Web\HomeController;
use App\Core\View;

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path   = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$path   = rtrim($path, '/') ?: '/';

/* ---------- GET routes ---------- */
if ($method === 'GET') {
    switch ($path) {
        case '/':                 (new HomeController())->index(); return;
        case '/coupons':          (new DealController())->coupons(); return;
        case '/referrals':        (new DealController())->referrals(); return;
        case '/login':            (new AuthController())->showLogin(); return;
        case '/register':         (new AuthController())->showRegister(); return;
        case '/dashboard':        (new AuthController())->myPosts(); return;
        case '/submit-coupon':    (new AuthController())->submitCouponForm(); return;
        case '/submit-referral':  (new AuthController())->submitReferralForm(); return;
        case '/admin':            (new DashboardController())->index(); return;
        case '/admin/coupons':    (new AdminCoupon())->index(); return;
        case '/admin/referrals':  (new AdminReferral())->index(); return;
        case '/admin/settings':   (new SettingsController())->index(); return;
        case '/admin/users':      (new SettingsController())->users(); return;
        case '/admin/audit':      (new SettingsController())->audit(); return;
    }

    // /coupon/{id}, /referral/{id} and click-through redirects /go/...
    if (preg_match('#^/coupon/(\d+)$#', $path, $m)) { (new DealController())->coupon((int) $m[1]); return; }
    if (preg_match('#^/referral/(\d+)$#', $path, $m)) { (new DealController())->referral((int) $m[1]); return; }
    if (preg_match('#^/go/coupon/(\d+)$#', $path, $m))  { (new DealController())->goCoupon((int) $m[1]); return; }
    if (preg_match('#^/go/referral/(\d+)$#', $path, $m)){ (new DealController())->goReferral((int) $m[1]); return; }

    View::renderError(404);
    return;
}

/* ---------- POST routes (all state changes; CSRF verified inside) ---------- */
if ($method === 'POST') {
    switch ($path) {
        case '/login':              (new AuthController())->login(); return;
        case '/logout':             (new AuthController())->logout(); return;
        case '/register':           (new AuthController())->register(); return;
        case '/submit-coupon':      (new AuthController())->submitCoupon(); return;
        case '/submit-referral':    (new AuthController())->submitReferral(); return;
        case '/admin/coupons/review': (new AdminCoupon())->review(); return;
        case '/admin/coupons/store':  (new AdminCoupon())->store(); return;
        case '/admin/coupons/delete': (new AdminCoupon())->remove(); return;
        case '/admin/referrals/review': (new AdminReferral())->review(); return;
        case '/admin/referrals/store':  (new AdminReferral())->store(); return;
        case '/admin/referrals/delete': (new AdminReferral())->remove(); return;
        case '/admin/settings':       (new SettingsController())->save(); return;
        case '/admin/users/add':      (new SettingsController())->addUser(); return;
        case '/admin/users/toggle':   (new SettingsController())->toggleUser(); return;
    }
    View::renderError(404);
    return;
}

http_response_code(405);
header('Allow: GET, POST');
