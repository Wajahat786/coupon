# Hostinger Deployment Guide (document root change ki zaroorat NAHI)

Hostinger shared hosting par `public_html` hi web root rehta hai — isme
koi problem nahi. Bas **app files ko public_html ke UPAR private folder
me rakho** aur sirf `public/` ka content `public_html` me daalo.

## Final server layout (EXACTLY aisa hona chahiye)

```text
/home/u123456789/                 ← Home directory (browser se inaccessible)
│
├── dealhub-app/                  ← PRIVATE code (yahan koi direct URL nahi pohanch sakta)
│   ├── .env                      ← DB credentials + APP_KEY
│   ├── app/                      ← bootstrap.php, Core/, Models/, Controllers/
│   ├── config/
│   ├── database/                 ← schema.sql, seed_admin.php
│   ├── views/
│   └── storage/logs/             ← PHP error log (writable)
│
└── public_html/                  ← WEB ROOT = sirf dealhub/public/ ka ANDAR ka content
    ├── .htaccess                 ← hidden file — upload karna MAT bhoolo
    ├── index.php                 ← SAARI routing isi ek file se hoti hai
    │                               (/admin, /coupons, /login sab index.php handle karta hai —
    │                                public/ me aur koi folder nahi banana)
    └── assets/
        ├── css/style.css
        └── js/theme.js, app.js
```

> ⚠️ **GHALTI jo 403 deti hai:** poora `dealhub/` folder zip karke
> `public_html/dealhub/` me extract karna. Isse `public_html/index.php`
> hota hi nahi → Apache directory listing block karta hai → **403**.
>
> ✅ Sahi: `public_html/index.php` **directly** maujood ho.

## Step-by-step (File Manager se)

1. **File Manager** kholo (hPanel → Files → File Manager). Home directory
   me `dealhub-app` naam ka new folder banao.
2. Project zip upload karo kahin bhi (temp), extract karo, phir:
   - `app`, `config`, `views`, `database`, `storage` folders **cut** karke
     `dealhub-app/` me **paste** karo.
   - `.env.example` ko bhi wahan paste karo, uska naam `.env` rakho
     (File Manager settings me "Show hidden files" ON karo), aur DB
     credentials + `APP_KEY` bhar do.
3. `dealhub/public/` ke **andar wali saari files** (`index.php`,
   `.htaccess`, `assets/`) select karke `public_html/` me paste karo.
4. Extra folders/files `public_html` se delete kar do (zip, temp etc.).
5. **Permissions:** saari files `644`, folders `755`. `.env` ko right-click
   → `640`. (Never 777.)
6. **PHP version:** hPanel → Advanced → PHP Configuration → **PHP 8.2**
   select karo, save. (Purana PHP 7.x → syntax error → blank/403.)
7. **Database:** hPanel → Databases → MySQL → naya DB + user banao, wahi
   credentials `.env` me daalo.
8. phpMyAdmin → import `dealhub-app/database/schema.sql`.
9. **Admin user:** hPanel → Advanced → SSH terminal (ya local se):
   ```bash
   cd ~/dealhub-app && php database/seed_admin.php
   ```
10. `https://yourdomain.com/` kholo → homepage, `/admin` → login.

## Agar phir bhi 403 aaye

| Check | Kaise |
|---|---|
| `public_html/index.php` exist karta hai? | File Manager me directly verify karo |
| `.htaccess` upload hua? | Hidden file hai — "Show hidden files" ON karke dekho |
| mod_rewrite on hai? | Hostinger default ON; agar custom htaccess rule fail ho to temporarily `.htaccess` rename karke test karo (homepage load ho to htaccess issue) |
| Folder nesting double? | `public_html/dealhub/public/...` ❌ → andar ki files upar shift karo |
| Ownership wrong (SSH se upload kiya)? | `chown -R u123456789:u123456789 ~/dealhub-app ~/public_html` |
| Actual error kya hai? | hPanel → Advanced → PHP Configuration → `display_errors` temporarily ON, ya `~/dealhub-app/storage/logs/php-error.log` dekho |

## Local XAMPP equivalent

```text
C:\xampp\htdocs\
├── dealhub-app\        (app, config, views, database, storage, .env)
└── public_html\        (public/ ka content)
```
Browser: `http://localhost/public_html/` — ya `httpd.conf` me ek
VirtualHost bana lo jiska DocumentRoot `htdocs/dealhub-app/public` ho.

## Auto-deploy script

Repo ke sath `deploy.sh` hai — SSH access hone par ek command me upar wala
structure bana deta hai:

```bash
./deploy.sh u123456789@your.hostinger.server /home/u123456789/dealhub-app
```
