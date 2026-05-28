<?php

declare(strict_types=1);

use App\Core\Application;
use App\Core\Env;
use App\Core\Session;

require dirname(__DIR__) . '/app/Config/helpers.php';
require dirname(__DIR__) . '/bootstrap/app.php';

Env::load(app_path('.env'));
date_default_timezone_set((string) config('app.timezone', 'America/Fortaleza'));
Session::start();

(new Application())->run();
