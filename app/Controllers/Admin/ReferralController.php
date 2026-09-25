<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Security;
use App\Core\View;
use App\Models\Audit;
use App\Models\ReferralLink;

final class ReferralController
{
    public function index(): void
    {
        Auth::requireLogin(true);
        $status = in_array($_GET['status'] ?? '', ReferralLink::STATUSES, true) ? $_GET['status'] : null;
        $page   = max(1, (int) ($_GET['page'] ?? 1));
        [$rows, $total, $perPage] = ReferralLink::listAll($status, $page, 25);

        View::render('admin/referrals', [
            'pageTitle' => 'Manage Referral Links',
            'referrals' => $rows,
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

        if ($id <= 0 || !isset($map[$action]) || ReferralLink::find($id) === null) {
            flash_message_set('error', 'Invalid request.');
            header('Location: /admin/referrals');
            return;
        }

        ReferralLink::setStatus($id, $map[$action], Auth::id());
        flash_message_set('success', "Referral link #$id → {$map[$action]}.");
        header('Location: ' . safe_next($_POST['return'] ?? null, '/admin/referrals'));
    }

    /** Admin creates a referral link directly (auto-approved). */
    public function store(): void
    {
        Auth::requireLogin(true);
        Security::verifyCsrf();

        $prog  = mb_substr(trim((string) ($_POST['program_name'] ?? '')), 0, 100);
        $title = mb_substr(trim((string) ($_POST['title'] ?? '')), 0, 150);
        $link  = trim((string) ($_POST['link'] ?? ''));

        if ($prog === '' || $title === '' || !preg_match('#^https://\S{8,500}$#i', $link)) {
            flash_message_set('error', 'Program, title and a valid https link are required.');
        } else {
            $id = ReferralLink::create($_POST + ['link' => $link], Auth::id());
            ReferralLink::setStatus($id, 'approved', Auth::id());
            flash_message_set('success', "Referral link #$id created & approved.");
        }
        header('Location: /admin/referrals');
    }

    public function remove(): void
    {
        Auth::requireLogin(true);
        Security::verifyCsrf();
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0 && ReferralLink::find($id) !== null) {
            ReferralLink::delete($id);
            Audit::log(Auth::id(), 'referral.delete', "referral#$id");
            flash_message_set('success', "Referral link #$id deleted.");
        }
        header('Location: /admin/referrals');
    }
}
