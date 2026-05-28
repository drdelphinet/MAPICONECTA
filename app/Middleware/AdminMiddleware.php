<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\View;

final class AdminMiddleware
{
    public function handle(Request $request): void
    {
        if (!Auth::check()) {
            redirect('entrar');
        }

        if (!Auth::hasRole(['administrador-geral', 'curador', 'editor'])) {
            http_response_code(403);
            View::render('errors/403', ['title' => 'Acesso negado'], 'layouts/public');
            exit;
        }
    }
}
