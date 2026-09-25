# DealHub — Coupons & Referral Links (plain PHP + MySQL)

Bina kisi framework ke, clean MVC structure wala coupon + referral listing site
jisme **public submissions → admin approval** workflow hai.

## ✨ Features
- 🎟️ Coupons: store, code, % / fixed / shipping discount, expiry, category, search & sort
- 🔗 Referral links: program, link, reward text, category
- ✅ Approval queue (pending → approved/rejected) — sirf approved cheezein public hoti hain
- 👤 User accounts (submitter) + Admin panel
- ⚙️ Site settings (site name, tagline, default theme) — DB me, admin se editable
- 🌙 Dark/Light theme toggle — user choice localStorage me save, server default setting se
- 📋 Copy-to-clipboard for codes/links, click counter, audit log, pagination

## 🔐 Security (kya-kya kiya hai)
| Threat | Defence |
|---|---|
| SQL injection | Har query PDO **prepared statements** se (`Database::run`), `ATTR_EMULATE_PREPARES=false` |
| XSS | Saara output `e()` = `htmlspecialchars(ENT_QUOTES)`; JS me innerHTML use nahi hota |
| CSRF | Har POST form me session token + `hash_equals` verify (`csrf_field()` / `Security::verifyCsrf()`) |
| Password attacks | `password_hash()` **bcrypt cost 12**, timing-safe login (dummy compare), min 10 chars |
| Session hijacking/fixation | `HttpOnly`, `Secure`, `SameSite=Lax` cookies; login par `session_regenerate_id(true)`; 30-min rotation |
| Brute force | DB-backed rate limiting: login 5 attempts/15min per IP+email, forms bhi throttled |
| Open redirect / phishing | Outbound links **https-only**, no creds/IP/localhost; `rel="noopener nofollow ugc"` |
| Privilege escalation | Role input se kabhi nahi liya jata (allow-list); last active admin disable nahi ho sakta; aap khud ko disable nahi kar sakte |
| Info leak | `.env`/config gitignore me, docroot sirf `public/`, DB errors log me jate hain output me nahi; error reporting OFF in production |
| Mass assignment | Models sirf allowed fields insert karte hain; settings keys allow-listed |

## 📁 Structure
```
app/            bootstrap.php, helpers.php
  Core/         Database, Auth, Security, RateLimit, View
  Models/       User, Coupon, ReferralLink, Setting, Audit
  Controllers/  Web/ (public) + Admin/
views/          layouts (main/admin/blank), pages, partials
public/         index.php (front controller), .htaccess, assets/
database/       schema.sql, seed_admin.php, nginx.conf.example
config/         config.php (env-driven)
```

## 🚀 XAMPP / Hostinger setup
1. Code upload karo (Hostinger pe `domains/yoursite.com/` ke andar, e.g. `dealhub/`).
   **Docroot sirf `dealhub/public/` folder hona chahiye** — baaki sab usse bahar.
2. phpMyAdmin me DB banao: `CREATE DATABASE dealhub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`
   phir `database/schema.sql` import karo.
3. `cp .env.example .env` aur values bharo (DB creds, APP_URL=https://yoursite.com, COOKIE_SECURE=true).
4. Admin banao (SSH ya XAMPP terminal):
   `php database/seed_admin.php "Your Name" "you@domain.com" "StrongPass123!"`
5. HTTPS enable karo (Hostinger free SSL) — tabhi Secure cookie + clipboard API kaam karegi.
6. `/admin` pe login karo → Settings me site name/tagline set karo.

Apache ke liye `public/.htaccess` ready hai; Nginx sample `database/nginx.conf.example` me.

## 🧪 Local test (XAMPP)
- Project `C:\xampp\htdocs\dealhub` me rakho, VirtualHost point kare `dealhub/public` par,
  ya simple testing ke liye: root folder se `php -S localhost:8080 -t public` chalao.
