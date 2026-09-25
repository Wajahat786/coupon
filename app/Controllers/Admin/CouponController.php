<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Security;
use App\Core\View;
use App\Models\Coupon;

final class CouponController
{
    public function index(): void
    {
        Auth::requireLogin(true);
        $status = in_array($_GET['status'] ?? '', Coupon::STATUSES, true) ? $_GET['status'] : null;
        $page   = max(1, (int) ($_GET['page'] ?? 1));
        [$rows, $total, $perPage] = Coupon::listAll($status, $page, 25);

        View::render('admin/coupons', [
            'pageTitle' => 'Manage Coupons',
            'coupons'   => $rows,
            'total'     => $total,
            'page'      => $page,
            'perPage'   => $perPage,
            'status'    => $status,
        ], 'layouts/admin');
    }

    public function review(): void
    {
        Auth::requireLogin(true);
        Security::verifyCsrf();

        $id     = (int) ($_POST['id'] ?? 0);
        $action = (string) ($_POST['action'] ?? '');
        $map    = ['approve' => 'approved', 'reject' => 'rejected', 'unpublish' => 'pending'];

        if ($id <= 0 || !isset($map[$action])) {
            flash_message_set('error', 'Invalid request.');
            header('Location: /admin/coupons');
            return;
        }
        if (Coupon::find($id) === null) {
            flash_message_set('error', 'Coupon not found.');
            header('Location: /admin/coupons');
            return;
        }

        Coupon::setStatus($id, $map[$action], Auth::id());
        flash_message_set('success', "Coupon #$id → {$map[$action]}.");
        header('Location: ' . safe_next($_POST['return'] ?? null, '/admin/coupons'));
    }

    /** Admin can also create coupons directly (auto-approved). */
    public function store(): void
    {
        Auth::requireLogin(true);
        Security::verifyCsrf();

        $code  = Security::normalizeCode((string) ($_POST['code'] ?? ''));
        $store = mb_substr(trim((string) ($_POST['store_name'] ?? '')), 0, 100);
        $title = mb_substr(trim((string) ($_POST['title'] ?? '')), 0, 150);
        $link  = trim((string) ($_POST['link'] ?? ''));

        if ($code === '' || $store === '' || $title === '') {
            flash_message_set('error', 'Store, title and code are required.');
        } elseif ($link !== '' && !preg_match('#^https://\S+$#i', $link)) {
            flash_message_set('error', 'Link must be a valid https URL.');
        } else {
            $id = Coupon::create($_POST + ['code' => $code, 'link' => $link], Auth::id());
            Coupon::setStatus($id, 'approved', Auth::id()); // admin-created = instant live
            flash_message_set('success', "Coupon #$id created & approved.");
        }
        header('Location: /admin/coupons');
    }

    public function remove(): void
    {
        Auth::requireLogin(true);
        Security::verifyCsrf();
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0 && Coupon::find($id) !== null) {
            Coupon::delete($id);
            \App\Models\Audit::log(Auth::id(), 'coupon.delete', "coupon#$id");
            flash_message_set('success', "Coupon #$id deleted.");
        }
        header('Location: /admin/coupons');
    }
}
