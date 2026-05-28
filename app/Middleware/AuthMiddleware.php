<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Session;

final class AuthMiddleware
{
    public function handle(Request $request): void
    {
        if (!Auth::check()) {
            Session::flash('message', [
                'type' => 'warning',
                'text' => 'Entre com sua conta para continuar.',
            ]);

            redirect('entrar');
        }
    }
}
