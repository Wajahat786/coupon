<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Referral links: user submits their referral URL + programme info,
 * admin approves before it goes public.
 */
final class ReferralLink
{
    public const STATUSES = ['pending', 'approved', 'rejected'];

    public static function create(array $d, int $userId): int
    {
        Database::run(
            'INSERT INTO referral_links
                (user_id, program_name, title, description, link, reward_text, category, status, created_at, updated_at)
             VALUES
                (:uid, :prog, :title, :desc, :link, :reward, :cat, \'pending\', NOW(), NOW())',
            [
                ':uid'    => $userId,
                ':prog'   => mb_substr(trim($d['program_name']), 0, 100),
                ':title'  => mb_substr(trim($d['title']), 0, 150),
                ':desc'   => mb_substr(trim($d['description'] ?? ''), 0, 2000),
                ':link'   => mb_substr(trim($d['link']), 0, 500),
                ':reward' => mb_substr(trim($d['reward_text'] ?? ''), 0, 150),
                ':cat'    => mb_substr(trim($d['category'] ?? 'General'), 0, 50),
            ]
        );
        return Database::lastInsertId();
    }

    public static function find(int $id): ?array
    {
        return Database::run(
            'SELECT r.*, u.name AS submitted_by FROM referral_links r
             LEFT JOIN users u ON u.id = r.user_id WHERE r.id = :id',
            [':id' => $id]
        )->fetch() ?: null;
    }

    public static function listApproved(string $search = '', string $category = '', int $page = 1, int $perPage = 12): array
    {
        $where  = ["status = 'approved'"];
        $params = [];
        if ($search !== '') {
            // distinct placeholders — named params may appear only once (emulation off)
            $where[] = '(program_name LIKE :s1 OR title LIKE :s2)';
            $like = '%' . addcslashes($search, '%_\\') . '%';
            $params[':s1'] = $like;
            $params[':s2'] = $like;
        }
        if ($category !== '') {
            $where[] = 'category = :cat';
            $params[':cat'] = $category;
        }
        $w = implode(' AND ', $where);
        $total  = (int) Database::run("SELECT COUNT(*) c FROM referral_links WHERE $w", $params)->fetch()['c'];
        $offset = max(0, ($page - 1) * $perPage);
        $rows   = Database::run(
            "SELECT r.*, u.name AS submitted_by FROM referral_links r
             LEFT JOIN users u ON u.id = r.user_id
             WHERE $w ORDER BY r.clicks DESC, r.created_at DESC LIMIT $perPage OFFSET $offset",
            $params
        )->fetchAll();
        return [$rows, $total, $perPage];
    }

    public static function listAll(?string $status = null, int $page = 1, int $perPage = 25): array
    {
        $params = [];
        $where  = '1=1';
        if ($status !== null && in_array($status, self::STATUSES, true)) {
            $where = 'status = :st';
            $params[':st'] = $status;
        }
        $total  = (int) Database::run("SELECT COUNT(*) c FROM referral_links r WHERE $where", $params)->fetch()['c'];
        $offset = max(0, ($page - 1) * $perPage);
        $rows   = Database::run(
            "SELECT r.*, u.name AS submitted_by FROM referral_links r
             LEFT JOIN users u ON u.id = r.user_id
             WHERE $where ORDER BY FIELD(r.status,'pending','approved','rejected'), r.created_at DESC
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
            'UPDATE referral_links SET status = :st, reviewed_by = :ad, reviewed_at = NOW(), updated_at = NOW() WHERE id = :id',
            [':st' => $status, ':ad' => $adminId, ':id' => $id]
        );
        Audit::log($adminId, "referral.$status", "referral#$id");
    }

    public static function delete(int $id): void
    {
        Database::run('DELETE FROM referral_links WHERE id = :id', [':id' => $id]);
    }

    public static function registerClick(int $id): void
    {
        Database::run('UPDATE referral_links SET clicks = clicks + 1 WHERE id = :id', [':id' => $id]);
    }

    public static function categories(): array
    {
        return array_column(
            Database::run("SELECT DISTINCT category FROM referral_links WHERE status='approved' ORDER BY category")->fetchAll(),
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
             FROM referral_links"
        )->fetch();
        return array_map(fn($v) => (int) $v, $r ?: []);
    }
}
