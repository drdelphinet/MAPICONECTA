<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function view(string $view, array $data = [], string $layout = 'layouts/public'): void
    {
        Response::view($view, $data, $layout);
    }

    protected function redirectWithMessage(string $path, string $type, string $message): never
    {
        Session::flash('message', [
            'type' => $type,
            'text' => $message,
        ]);

        redirect($path);
    }
}
