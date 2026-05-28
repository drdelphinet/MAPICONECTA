<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Models\Municipality;

final class CurationController extends Controller
{
    public function __construct(
        private readonly Municipality $municipalities = new Municipality()
    ) {
    }

    public function index(Request $request): void
    {
        $status = trim((string) $request->input('status', 'enviado_revisao'));

        $this->view('admin/curation/index', [
            'title' => 'Curadoria',
            'user' => Auth::user(),
            'status' => $status,
            'summary' => $this->municipalities->workflowSummary(),
            'items' => $this->municipalities->queue($status),
        ], 'layouts/admin');
    }
}
