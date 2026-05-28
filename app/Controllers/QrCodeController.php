<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Models\Municipality;
use App\Models\Qrcode;

final class QrCodeController extends Controller
{
    public function __construct(
        private readonly Municipality $municipalities = new Municipality(),
        private readonly Qrcode $qrcodes = new Qrcode()
    ) {
    }

    public function municipality(Request $request): void
    {
        $municipality = $this->municipalities->findPublishedBySlug((string) $request->route('slug'));
        if ($municipality === null) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'QR Code nao encontrado']);
            return;
        }

        $qrcode = $this->qrcodes->ensureForMunicipality($municipality);
        if (!empty($qrcode['id_qrcode'])) {
            $this->qrcodes->registerAccess((int) $qrcode['id_qrcode'], 'qrcode_publico');
        }

        $user = Auth::user();
        if ($user !== null) {
            $this->municipalities->markVisitedIfMissing((int) $municipality['id_municipio'], (int) $user['id'], 'qrcode');
        }

        redirect('municipio/' . (string) $municipality['slug']);
    }
}
