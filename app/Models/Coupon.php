<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Statuses: pending -> approved | rejected. Approved listings appear publicly.
 */
final class Coupon
{
    public const STATUSES = ['pending', 'approved', 'rejected'];

    public static function create(array $d, int $userId): int
    {
        Database::run(
            'INSERT INTO coupons
                (user_id, store_name, title, description, link, code, discount_type, discount_value,
                 category, expires_at, max_uses, uses_count, status, created_at, updated_at)
             VALUES
                (:uid, :store, :title, :desc, :link, :code, :dtype, :dval,
                 :cat, :exp, :max, 0, \'pending\', NOW(), NOW())',
            [
                ':uid'   => $userId,
                ':store' => mb_substr(trim($d['store_name']), 0, 100),
                ':title' => mb_substr(trim($d['title']), 0, 150),
                ':desc'  => mb_substr(trim($d['description'] ?? ''), 0, 2000),
                ':link'  => mb_substr(trim($d['link'] ?? ''), 0, 500),
                ':code'  => $d['code'],
                ':dtype' => in_array($d['discount_type'] ?? '', ['percent', 'fixed', 'shipping'], true) ? $d['discount_type'] : 'percent',
                ':dval'  => max(0, min(1000000, (float) ($d['discount_value'] ?? 0))),
                ':cat'   => mb_substr(trim($d['category'] ?? 'General'), 0, 50),
                ':exp'   => !empty($d['expires_at']) ? $d['expires_at'] . ' 23:59:59' : null,
                ':max'   => isset($d['max_uses']) && (int) $d['max_uses'] > 0 ? (int) $d['max_uses'] : null,
            ]
        );
        return Database::lastInsertId();
    }

    public static function find(int $id): ?array
    {
        return Database::run(
            'SELECT c.*, u.name AS submitted_by FROM coupons c
             LEFT JOIN users u ON u.id = c.user_id WHERE c.id = :id',
            [':id' => $id]
        )->fetch() ?: null;
    }

    /** Public listing: only approved, non-expired. Whitelisted sort columns only. */
    public static function listApproved(string $search = '', string $category = '', string $sort = 'newest', int $page = 1, int $perPage = 12): array
    {
        $orderWhitelist = [
            'newest'   => 'c.created_at DESC',
            'popular'  => 'c.clicks DESC, c.created_at DESC',
            'expiry'   => 'c.expires_at IS NULL, c.expires_at ASC',
        ];
        $order = $orderWhitelist[$sort] ?? $orderWhitelist['newest'];

        $where  = ["c.status = 'approved'", '(c.expires_at IS NULL OR c.expires_at > NOW())'];
        $params = [];
        if ($search !== '') {
            // NOTE: with emulation off, a named placeholder may appear only ONCE
            // per query — use distinct :s1/:s2/:s3 bound to the same value.
            $where[] = '(c.store_name LIKE :s1 OR c.title LIKE :s2 OR c.code LIKE :s3)';
            $like = '%' . addcslashes($search, '%_\\') . '%';
            $params[':s1'] = $like;
            $params[':s2'] = $like;
            $params[':s3'] = $like;
        }
        if ($category !== '') {
            $where[] = 'c.category = :cat';
            $params[':cat'] = $category;
        }

        $total = (int) Database::run(
            'SELECT COUNT(*) c FROM coupons c WHERE ' . implode(' AND ', $where), $params
        )->fetch()['c'];

        $offset = max(0, ($page - 1) * $perPage);
        // LIMIT/OFFSET are ints — safe to inline after casting.
        $rows = Database::run(
            'SELECT c.*, u.name AS submitted_by FROM coupons c
             LEFT JOIN users u ON u.id = c.user_id
             WHERE ' . implode(' AND ', $where) . "
             ORDER BY $order LIMIT $perPage OFFSET $offset",
            $params
        )->fetchAll();

        return [$rows, $total, $perPage];
    }

    /** Admin listing by any status. */
    public static function listAll(?string $status = null, int $page = 1, int $perPage = 25): array
    {
        $params = [];
        $where  = '1=1';
        if ($status !== null && in_array($status, self::STATUSES, true)) {
            $where = 'c.status = :st';
            $params[':st'] = $status;
        }
        $total  = (int) Database::run("SELECT COUNT(*) c FROM coupons c WHERE $where", $params)->fetch()['c'];
        $offset = max(0, ($page - 1) * $perPage);
        $rows   = Database::run(
            "SELECT c.*, u.name AS submitted_by FROM coupons c
             LEFT JOIN users u ON u.id = c.user_id
             WHERE $where ORDER BY FIELD(c.status,'pending','approved','rejected'), c.created_at DESC
             LIMIT $perPage OFFSET $offset",
            $params
        )->fetchAll();
        return [$rows, $total, $perPage];
    }

    public static function setStatus(int $id, string $status, int $adminId): void
    {
        if (!in_array($status, self::STATUSES, true)) {
            return;
        }
        Database::run(
            'UPDATE coupons SET status = :st, reviewed_by = :ad, reviewed_at = NOW(), updated_at = NOW() WHERE id = :id',
            [':st' => $status, ':ad' => $adminId, ':id' => $id]
        );
        Audit::log($adminId, "coupon.$status", "coupon#$id");
    }

    public static function delete(int $id): void
    {
        Database::run('DELETE FROM coupons WHERE id = :id', [':id' => $id]);
    }

    public static function registerClick(int $id): void
    {
        Database::run('UPDATE coupons SET clicks = clicks + 1 WHERE id = :id', [':id' => $id]);
    }

    public static function categories(): array
    {
        return array_column(
            Database::run("SELECT DISTINCT category FROM coupons WHERE status='approved' ORDER BY category")->fetchAll(),
            'category'
        );
    }

    public static function stats(): array
    {
        $r = Database::run(
            "SELECT
               SUM(status='pending')  p,
               SUM(status='approved') a,
               SUM(status='rejected') rj,
               COUNT(*) total,
               COALESCE(SUM(clicks),0) clicks
             FROM coupons"
        )->fetch();
        return array_map(fn($v) => (int) $v, $r ?: []);
    }
}
