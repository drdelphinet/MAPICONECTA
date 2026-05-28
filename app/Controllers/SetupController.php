<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;

final class SetupController extends Controller
{
    public function show(Request $request): void
    {
        $this->view('setup/index', [
            'title' => 'Instalacao',
            'databaseStatus' => Database::status(),
        ]);
    }
}
