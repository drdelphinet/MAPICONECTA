<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Models\EducationActivity;
use App\Models\MunicipalityMedia;
use App\Models\PointOfInterest;
use App\Models\Qrcode;
use App\Models\Quiz;

final class MediaController extends Controller
{
    public function __construct(
        private readonly MunicipalityMedia $media = new MunicipalityMedia(),
        private readonly Quiz $quizzes = new Quiz(),
        private readonly PointOfInterest $points = new PointOfInterest(),
        private readonly EducationActivity $activities = new EducationActivity(),
        private readonly Qrcode $qrcodes = new Qrcode()
    ) {
    }

    public function show(Request $request): void
    {
        $mediaItem = $this->media->findPublished((int) $request->route('id'));
        if ($mediaItem === null) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Midia nao encontrada']);
            return;
        }

        $this->view('media/show', [
            'title' => $mediaItem['titulo'],
            'user' => Auth::user(),
            'mediaItem' => $mediaItem,
            'publicUrl' => absolute_url('midia/' . (string) $mediaItem['id_midia']),
            'qrTrackingUrl' => $this->qrcodes->trackingUrlForMunicipality([
                'slug' => $mediaItem['municipio_slug'],
            ]),
            'qrImageUrl' => $this->qrcodes->imageUrl($this->qrcodes->trackingUrlForMunicipality([
                'slug' => $mediaItem['municipio_slug'],
            ]), 280),
            'relatedQuizzes' => $this->quizzes->publicByMunicipality((int) $mediaItem['id_municipio']),
            'relatedPoints' => $this->points->publicByMunicipality((int) $mediaItem['id_municipio'], 4),
            'relatedActivities' => $this->activities->publicByMunicipality((int) $mediaItem['id_municipio'], 3),
            'otherMedia' => array_values(array_filter(
                $this->media->publicByMunicipality((int) $mediaItem['id_municipio'], 6),
                static fn (array $candidate): bool => (int) $candidate['id_midia'] !== (int) $mediaItem['id_midia']
            )),
        ]);
    }
}
