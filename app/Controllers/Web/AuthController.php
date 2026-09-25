<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Auth;
use App\Core\Security;
use App\Core\View;
use App\Models\Coupon;
use App\Models\ReferralLink;
use App\Models\User;

/**
 * Login / register, public submissions (go to admin queue), user "my posts".
 */
final class AuthController
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            header('Location: ' . (Auth::isAdmin() ? '/admin' : '/dashboard'));
            return;
        }
        View::render('auth/login', ['pageTitle' => 'Sign in'], 'layouts/blank');
    }

    public function login(): void
    {
        Security::verifyCsrf();

        $email    = mb_substr(trim((string) ($_POST['email'] ?? '')), 0, 190);
        $password = (string) ($_POST['password'] ?? '');

        // Basic throttle on the form itself too.
        if (!Security::rateCheck('form:login:' . Security::clientIp(), 10, 600)) {
            View::renderError(429);
            return;
        }

        if ($email === '' || $password === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash_message_set('error', 'Invalid email or password.');
            header('Location: /login');
            return;
        }

        if (Auth::attempt($email, $password)) {
            $next = safe_next($_POST['next'] ?? null, Auth::isAdmin() ? '/admin' : '/dashboard');
            header('Location: ' . $next);
            return;
        }

        flash_message_set('error', 'Invalid email or password.');
        header('Location: /login');
    }

    public function logout(): void
    {
        Security::verifyCsrf(); // logout must be POST-only (CSRF defence)
        Auth::logout();
        header('Location: /');
    }

    public function showRegister(): void
    {
        if (Auth::check()) {
            header('Location: /');
            return;
        }
        View::render('auth/register', ['pageTitle' => 'Create account'], 'layouts/blank');
    }

    public function register(): void
    {
        Security::verifyCsrf();

        if (!Security::rateCheck('form:register:' . Security::clientIp(), 5, 3600)) {
            View::renderError(429);
            return;
        }

        $name     = mb_substr(trim((string) ($_POST['name'] ?? '')), 0, 100);
        $email    = mb_substr(trim((string) ($_POST['email'] ?? '')), 0, 190);
        $password = (string) ($_POST['password'] ?? '');
        $confirm  = (string) ($_POST['password_confirm'] ?? '');

        $err = null;
        if (mb_strlen($name) < 2)                          $err = 'Name must be at least 2 characters.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $err = 'Please enter a valid email address.';
        elseif (strlen($password) < 10)                     $err = 'Password must be at least 10 characters.';
        elseif (!hash_equals($password, $confirm))          $err = 'Passwords do not match.';
        elseif (User::findByEmail($email))                  $err = 'An account with this email already exists.';

        if ($err !== null) {
            flash_message_set('error', $err);
            header('Location: /register');
            return;
        }

        // Role is ALWAYS submitter — never taken from input (privilege-escalation defence).
        User::create($name, $email, $password, 'submitter');
        Auth::attempt($email, $password);
        flash_message_set('success', 'Welcome! Your submissions will appear after admin approval.');
        header('Location: /dashboard');
    }

    /* ---------------- Public submission forms ---------------- */

    public function submitCouponForm(): void
    {
        View::render('submit/coupon', ['pageTitle' => 'Submit a Coupon', 'categories' => Coupon::categories()]);
    }

    public function submitCoupon(): void
    {
        Security::verifyCsrf();
        if (!Auth::check()) {
            header('Location: /login?next=/submit-coupon');
            return;
        }
        if (!Security::rateCheck('form:coupon:' . Auth::id(), 10, 3600)) {
            flash_message_set('error', 'You have submitted too many today. Try later.');
            header('Location: /submit-coupon');
            return;
        }

        $code  = Security::normalizeCode((string) ($_POST['code'] ?? ''));
        $store = mb_substr(trim((string) ($_POST['store_name'] ?? '')), 0, 100);
        $title = mb_substr(trim((string) ($_POST['title'] ?? '')), 0, 150);
        $link  = trim((string) ($_POST['link'] ?? ''));

        if ($code === '' || $store === '' || $title === '') {
            flash_message_set('error', 'Store, title and code are required.');
            header('Location: /submit-coupon');
            return;
        }
        if ($link !== '' && !preg_match('#^https://[^\s]+$#i', $link)) {
            flash_message_set('error', 'Deal link must be a valid https:// URL.');
            header('Location: /submit-coupon');
            return;
        }

        Coupon::create($_POST + ['code' => $code, 'link' => $link], Auth::id());
        flash_message_set('success', 'Submitted! It will go live after admin approval.');
        header('Location: /dashboard');
    }

    public function submitReferralForm(): void
    {
        View::render('submit/referral', ['pageTitle' => 'Share a Referral Link', 'categories' => ReferralLink::categories()]);
    }

    public function submitReferral(): void
    {
        Security::verifyCsrf();
        if (!Auth::check()) {
            header('Location: /login?next=/submit-referral');
            return;
        }
        if (!Security::rateCheck('form:referral:' . Auth::id(), 10, 3600)) {
            flash_message_set('error', 'Too many submissions. Try later.');
            header('Location: /submit-referral');
            return;
        }

        $prog  = mb_substr(trim((string) ($_POST['program_name'] ?? '')), 0, 100);
        $title = mb_substr(trim((string) ($_POST['title'] ?? '')), 0, 150);
        $link  = trim((string) ($_POST['link'] ?? ''));

        if ($prog === '' || $title === '' || !preg_match('#^https://[^\s]{8,500}$#i', $link)) {
            flash_message_set('error', 'Program name, title and a valid https:// referral link are required.');
            header('Location: /submit-referral');
            return;
        }

        ReferralLink::create($_POST + ['link' => $link], Auth::id());
        flash_message_set('success', 'Referral link submitted for review!');
        header('Location: /dashboard');
    }

    /* ---------------- User dashboard ("my posts") ---------------- */

    public function myPosts(): void
    {
        Auth::requireLogin();
        $uid = Auth::id();
        $coupons   = \App\Core\Database::run(
            'SELECT * FROM coupons WHERE user_id = :u ORDER BY id DESC LIMIT 100', [':u' => $uid])->fetchAll();
        $referrals = \App\Core\Database::run(
            'SELECT * FROM referral_links WHERE user_id = :u ORDER BY id DESC LIMIT 100', [':u' => $uid])->fetchAll();

        View::render('dashboard', ['pageTitle' => 'My Submissions', 'coupons' => $coupons, 'referrals' => $referrals]);
    }
}
