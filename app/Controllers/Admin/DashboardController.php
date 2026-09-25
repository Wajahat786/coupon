<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Security;
use App\Core\View;
use App\Models\Coupon;
use App\Models\ReferralLink;
use App\Models\Audit;

/**
 * Admin dashboard — every mutating action is POST + CSRF + admin-only,
 * and written to the audit log.
 */
final class DashboardController
{
    public function index(): void
    {
        Auth::requireLogin(true);

        $recentCoupons   = Database::run('SELECT id,store_name,title,status,created_at FROM coupons ORDER BY id DESC LIMIT 8')->fetchAll();
        $recentReferrals = Database::run('SELECT id,program_name,title,status,created_at FROM referral_links ORDER BY id DESC LIMIT 8')->fetchAll();

        View::render('admin/dashboard', [
            'pageTitle'       => 'Admin Dashboard',
            'couponStats'     => Coupon::stats(),
            'referralStats'   => ReferralLink::stats(),
            'recentCoupons'   => $recentCoupons,
            'recentReferrals' => $recentReferrals,
            'audit'           => Audit::recent(15),
        ], 'layouts/admin');
    }
}
