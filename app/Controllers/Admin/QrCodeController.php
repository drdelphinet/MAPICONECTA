<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

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

    public function showMunicipality(Request $request): void
    {
        $municipality = $this->municipalities->find((int) $request->route('id'));
        if ($municipality === null) {
            $this->redirectWithMessage('admin/municipios', 'error', 'Municipio nao encontrado.');
        }

        $qrcode = $this->qrcodes->ensureForMunicipality($municipality);
        $trackingUrl = $this->qrcodes->trackingUrlForMunicipality($municipality);

        $this->view('admin/municipalities/qrcode', [
            'title' => 'QR Code do municipio',
            'user' => Auth::user(),
            'municipality' => $municipality,
            'qrcode' => $qrcode,
            'trackingUrl' => $trackingUrl,
            'publicUrl' => absolute_url('municipio/' . (string) $municipality['slug']),
            'qrImageUrl' => $this->qrcodes->imageUrl($trackingUrl, 420),
        ], 'layouts/admin');
    }
}
