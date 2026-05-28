<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Models\EducationActivity;
use App\Models\Municipality;
use App\Models\MunicipalityMedia;
use App\Models\PointOfInterest;
use App\Models\Qrcode;
use App\Models\Quiz;

final class EducationController extends Controller
{
    public function __construct(
        private readonly EducationActivity $activities = new EducationActivity(),
        private readonly Municipality $municipalities = new Municipality(),
        private readonly PointOfInterest $points = new PointOfInterest(),
        private readonly MunicipalityMedia $media = new MunicipalityMedia(),
        private readonly Quiz $quizzes = new Quiz(),
        private readonly Qrcode $qrcodes = new Qrcode()
    ) {
    }

    public function index(Request $request): void
    {
        $municipalitySlug = trim((string) $request->input('municipio', ''));
        $discipline = trim((string) $request->input('disciplina', ''));
        $schoolYear = trim((string) $request->input('ano', ''));

        $publishedMunicipalities = $this->municipalities->publicList();
        $selectedMunicipality = null;

        foreach ($publishedMunicipalities as $municipality) {
            if (($municipality['slug'] ?? '') === $municipalitySlug) {
                $selectedMunicipality = $municipality;
                break;
            }
        }

        $filterOptions = $this->activities->filterOptions();
        $activityList = $this->activities->publicList(
            $selectedMunicipality ? (int) $selectedMunicipality['id_municipio'] : null,
            $discipline,
            $schoolYear
        );

        $this->view('education/index', [
            'title' => 'MAPI Educacao',
            'user' => Auth::user(),
            'activities' => $activityList,
            'municipalities' => $publishedMunicipalities,
            'selectedMunicipalitySlug' => $municipalitySlug,
            'selectedDiscipline' => $discipline,
            'selectedSchoolYear' => $schoolYear,
            'disciplineOptions' => $filterOptions['disciplinas'],
            'schoolYearOptions' => $filterOptions['anos'],
        ]);
    }

    public function show(Request $request): void
    {
        $activity = $this->activities->findPublished((int) $request->route('id'));
        if ($activity === null) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Atividade nao encontrada']);
            return;
        }

        $this->view('education/show', [
            'title' => $activity['titulo'],
            'user' => Auth::user(),
            'activity' => $activity,
            'publicUrl' => absolute_url('mapi-educacao/atividade/' . (string) $activity['id_atividade']),
            'qrTrackingUrl' => $this->qrcodes->trackingUrlForMunicipality([
                'slug' => $activity['municipio_slug'],
            ]),
            'qrImageUrl' => $this->qrcodes->imageUrl($this->qrcodes->trackingUrlForMunicipality([
                'slug' => $activity['municipio_slug'],
            ]), 280),
            'relatedQuizzes' => $this->quizzes->publicByMunicipality((int) $activity['id_municipio']),
            'relatedPoints' => $this->points->publicByMunicipality((int) $activity['id_municipio'], 3),
            'relatedMedia' => $this->media->publicByMunicipality((int) $activity['id_municipio'], 3),
            'otherActivities' => array_values(array_filter(
                $this->activities->publicByMunicipality((int) $activity['id_municipio'], 6),
                static fn (array $candidate): bool => (int) $candidate['id_atividade'] !== (int) $activity['id_atividade']
            )),
        ]);
    }
}
