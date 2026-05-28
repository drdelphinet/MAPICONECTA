<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;

final class GuestMiddleware
{
    public function handle(Request $request): void
    {
        if (Auth::check()) {
            redirect('meu-mapi');
        }
    }
}
