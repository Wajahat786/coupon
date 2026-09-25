<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Auth;
use App\Core\Security;
use App\Core\View;
use App\Models\Coupon;
use App\Models\ReferralLink;

/**
 * Public pages: coupon/referral listings, detail, validated outbound redirect.
 */
final class DealController
{
    public function coupons(): void
    {
        $search   = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 80);
        $category = Security::slug((string) ($_GET['cat'] ?? ''));
        $sort     = (string) ($_GET['sort'] ?? 'newest');
        $page     = max(1, (int) ($_GET['page'] ?? 1));

        [$rows, $total, $perPage] = Coupon::listApproved($search, $category, $sort, $page, 12);

        View::render('coupons/index', [
            'pageTitle'  => ($search !== '' ? "Search: $search — " : '') . 'Coupons',
            'coupons'    => $rows,
            'total'      => $total,
            'page'       => $page,
            'perPage'    => $perPage,
            'search'     => $search,
            'category'   => $category,
            'sort'       => $sort,
            'categories' => Coupon::categories(),
        ]);
    }

    public function coupon(int $id): void
    {
        $c = Coupon::find($id);
        if ($c === null || $c['status'] !== 'approved') {
            View::renderError(404);
            return;
        }
        View::render('coupons/show', ['pageTitle' => $c['title'], 'coupon' => $c]);
    }

    /**
     * Outbound click-through. Validates the stored URL strictly before any
     * redirect so the site can never be used as an open-redirect / phishing hop.
     */
    public function goCoupon(int $id): void
    {
        $c = Coupon::find($id);
        $url = self::safeTarget((string) ($c['link'] ?? ''));
        if ($c === null || $c['status'] !== 'approved' || $url === null) {
            View::renderError(404);
            return;
        }
        Coupon::registerClick($id);
        header('Location: ' . $url, true, 302);
        header('X-Robots-Tag: noindex');
        exit;
    }

    public function referrals(): void
    {
        $search   = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 80);
        $category = Security::slug((string) ($_GET['cat'] ?? ''));
        $page     = max(1, (int) ($_GET['page'] ?? 1));

        [$rows, $total, $perPage] = ReferralLink::listApproved($search, $category, $page, 12);

        View::render('referrals/index', [
            'pageTitle'  => 'Referral Links',
            'referrals'  => $rows,
            'total'      => $total,
            'page'       => $page,
            'perPage'    => $perPage,
            'search'     => $search,
            'category'   => $category,
            'categories' => ReferralLink::categories(),
        ]);
    }

    public function referral(int $id): void
    {
        $r = ReferralLink::find($id);
        if ($r === null || $r['status'] !== 'approved') {
            View::renderError(404);
            return;
        }
        View::render('referrals/show', ['pageTitle' => $r['title'], 'referral' => $r]);
    }

    public function goReferral(int $id): void
    {
        $r = ReferralLink::find($id);
        $url = self::safeTarget($r['link'] ?? '');
        if ($r === null || $r['status'] !== 'approved' || $url === null) {
            View::renderError(404);
            return;
        }
        ReferralLink::registerClick($id);
        header('Location: ' . $url, true, 302);
        exit;
    }

    /** Strict allow-list for outbound URLs: https only, no creds, no javascript:. */
    private static function safeTarget(string $raw): ?string
    {
        $parts = parse_url(trim($raw));
        if (!$parts || ($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])) {
            return null;
        }
        if (isset($parts['user']) || isset($parts['pass'])) {
            return null;
        }
        $host = strtolower($parts['host']);
        // block obvious internal targets
        if (filter_var($host, FILTER_VALIDATE_IP) || str_ends_with($host, '.local') || $host === 'localhost') {
            return null;
        }
        return $raw;
    }
}
