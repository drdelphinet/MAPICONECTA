<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class View
{
    public static function render(string $view, array $data = [], string $layout = 'layouts/public'): void
    {
        $viewFile = app_path('app/Views/' . $view . '.php');
        $layoutFile = app_path('app/Views/' . $layout . '.php');

        if (!file_exists($viewFile)) {
            throw new RuntimeException("View nao encontrada: {$view}");
        }

        if (!file_exists($layoutFile)) {
            throw new RuntimeException("Layout nao encontrado: {$layout}");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewFile;
        $content = (string) ob_get_clean();

        ob_start();
        require $layoutFile;
        $html = (string) ob_get_clean();

        echo normalize_visible_portuguese($html);
    }
}
