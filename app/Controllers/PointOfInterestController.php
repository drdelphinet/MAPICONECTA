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

final class PointOfInterestController extends Controller
{
    public function __construct(
        private readonly PointOfInterest $points = new PointOfInterest(),
        private readonly Quiz $quizzes = new Quiz(),
        private readonly MunicipalityMedia $media = new MunicipalityMedia(),
        private readonly EducationActivity $activities = new EducationActivity(),
        private readonly Qrcode $qrcodes = new Qrcode()
    ) {
    }

    public function show(Request $request): void
    {
        $point = $this->points->findPublished((int) $request->route('id'));
        if ($point === null) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Ponto turistico nao encontrado']);
            return;
        }

        $this->view('tourism/show', [
            'title' => $point['nome'],
            'user' => Auth::user(),
            'point' => $point,
            'publicUrl' => absolute_url('turismo/' . (string) $point['id_ponto_turistico']),
            'municipalityUrl' => absolute_url('municipio/' . (string) $point['municipio_slug']),
            'qrTrackingUrl' => $this->qrcodes->trackingUrlForMunicipality([
                'slug' => $point['municipio_slug'],
            ]),
            'qrImageUrl' => $this->qrcodes->imageUrl($this->qrcodes->trackingUrlForMunicipality([
                'slug' => $point['municipio_slug'],
            ]), 280),
            'relatedQuizzes' => $this->quizzes->publicByMunicipality((int) $point['id_municipio']),
            'relatedMedia' => $this->media->publicByMunicipality((int) $point['id_municipio'], 4),
            'relatedActivities' => $this->activities->publicByMunicipality((int) $point['id_municipio'], 3),
            'otherPoints' => array_values(array_filter(
                $this->points->publicByMunicipality((int) $point['id_municipio'], 6),
                static fn (array $candidate): bool => (int) $candidate['id_ponto_turistico'] !== (int) $point['id_ponto_turistico']
            )),
        ]);
    }
}
