<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Auth;
use App\Core\Security;
use App\Core\View;
use App\Models\Coupon;
use App\Models\ReferralLink;

final class HomeController
{
    public function index(): void
    {
        [$coupons] = Coupon::listApproved('', '', 'newest', 1, 6);
        [$referrals] = ReferralLink::listApproved('', '', 1, 6);

        View::render('home', [
            'pageTitle' => setting('site_name', APP_CONFIG['app']['name']) . ' — Coupons & Referral Deals',
            'coupons'   => $coupons,
            'referrals' => $referrals,
            'categories'=> Coupon::categories(),
        ]);
    }
}
