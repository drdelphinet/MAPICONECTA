<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public static function view(string $view, array $data = [], string $layout = 'layouts/public'): void
    {
        View::render($view, $data, $layout);
    }

    public static function json(array $payload, int $statusCode = 200): never
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
