-- ============================================================
-- DealHub schema (MySQL 8.0+ / utf8mb4)
-- Import:  mysql -u root -p dealhub < database/schema.sql
-- ============================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100)  NOT NULL,
    email         VARCHAR(190)  NOT NULL UNIQUE,
    password_hash VARCHAR(255)  NOT NULL,
    role          ENUM('admin','submitter') NOT NULL DEFAULT 'submitter',
    is_active     TINYINT(1)    NOT NULL DEFAULT 1,
    last_login_at DATETIME      NULL,
    last_login_ip VARCHAR(45)   NULL,
    created_at    DATETIME      NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS coupons (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id        INT UNSIGNED NULL,
    store_name     VARCHAR(100)  NOT NULL,
    title          VARCHAR(150)  NOT NULL,
    description    VARCHAR(2000) NOT NULL DEFAULT '',
    link           VARCHAR(500)  NOT NULL DEFAULT '',
    code           VARCHAR(32)   NOT NULL,
    discount_type  ENUM('percent','fixed','shipping') NOT NULL DEFAULT 'percent',
    discount_value DECIMAL(10,2) NOT NULL DEFAULT 0,
    category       VARCHAR(50)   NOT NULL DEFAULT 'General',
    expires_at     DATETIME      NULL,
    max_uses       INT UNSIGNED  NULL,
    uses_count     INT UNSIGNED  NOT NULL DEFAULT 0,
    clicks         INT UNSIGNED  NOT NULL DEFAULT 0,
    status         ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    reviewed_by    INT UNSIGNED  NULL,
    reviewed_at    DATETIME      NULL,
    created_at     DATETIME      NOT NULL,
    updated_at     DATETIME      NOT NULL,
    INDEX idx_status_created (status, created_at),
    INDEX idx_category (category),
    FULLTEXT idx_search (store_name, title, description),
    CONSTRAINT fk_coupon_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_coupon_admin FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS referral_links (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id      INT UNSIGNED NULL,
    program_name VARCHAR(100)  NOT NULL,
    title        VARCHAR(150)  NOT NULL,
    description  VARCHAR(2000) NOT NULL DEFAULT '',
    link         VARCHAR(500)  NOT NULL,
    reward_text  VARCHAR(150)  NOT NULL DEFAULT '',
    category     VARCHAR(50)   NOT NULL DEFAULT 'General',
    clicks       INT UNSIGNED  NOT NULL DEFAULT 0,
    status       ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    reviewed_by  INT UNSIGNED  NULL,
    reviewed_at  DATETIME      NULL,
    created_at   DATETIME      NOT NULL,
    updated_at   DATETIME      NOT NULL,
    INDEX idx_status_created (status, created_at),
    CONSTRAINT fk_ref_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_ref_admin FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
    s_key   VARCHAR(64)  NOT NULL PRIMARY KEY,
    s_value VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rate_limits (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    rl_key     CHAR(64)     NOT NULL,           -- sha256 of "action:ip:identifier"
    created_at DATETIME     NOT NULL,
    INDEX idx_key_time (rl_key, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NULL,
    action     VARCHAR(100) NOT NULL,
    target     VARCHAR(190) NOT NULL DEFAULT '',
    ip         VARCHAR(45)  NOT NULL DEFAULT '',
    created_at DATETIME     NOT NULL,
    INDEX idx_created (created_at),
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Do NOT insert a default admin here. Create your own on the server with:
--   php database/seed_admin.php "Your Name" "you@domain.com" "StrongPass123!"

INSERT INTO settings (s_key, s_value) VALUES
 ('site_name', 'DealHub'),
 ('tagline', 'Best Coupons & Referral Links, Hand-Approved'),
 ('default_theme', 'dark'),
 ('require_approval', '1')
ON DUPLICATE KEY UPDATE s_value = VALUES(s_value);
