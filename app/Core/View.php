<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Minimal view renderer with layout support. All output is escaped in the
 * templates themselves via e().
 */
final class View
{
    public static function render(string $template, array $data = [], string $layout = 'layouts/main'): void
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require BASE_PATH . '/views/' . $template . '.php';
        $content = ob_get_clean();

        require BASE_PATH . '/views/' . $layout . '.php';
    }

    /** Standalone JSON response (API endpoints). */
    public static function json(array $payload, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        exit;
    }

    public static function renderError(int $code): void
    {
        http_response_code($code);
        $titles = [403 => 'Access Denied', 404 => 'Page Not Found', 429 => 'Too Many Requests'];
        $title  = $titles[$code] ?? 'Error';
        require BASE_PATH . '/views/errors/generic.php';
    }
}
