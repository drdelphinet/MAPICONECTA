<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

final class Application
{
    public function run(): void
    {
        $router = new Router();
        $request = new Request();

        require app_path('routes/web.php');

        try {
            $router->dispatch($request);
        } catch (Throwable $exception) {
            http_response_code(500);

            $databaseStatus = Database::status();

            if (!$databaseStatus['ready']) {
                View::render('setup/index', [
                    'title' => 'Instalacao',
                    'databaseStatus' => $databaseStatus,
                ], 'layouts/public');
                return;
            }

            View::render('errors/500', [
                'title' => 'Erro interno',
                'exception' => $exception,
                'debug' => filter_var(config('app.debug', false), FILTER_VALIDATE_BOOL),
            ], 'layouts/public');
        }
    }
}
