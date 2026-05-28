<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Models\Municipality;
use App\Models\MunicipalityMedia;
use App\Models\PointOfInterest;
use App\Models\Quiz;
use App\Models\State;
use Throwable;

final class HomeController extends Controller
{
    public function __construct(
        private readonly State $states = new State(),
        private readonly Municipality $municipalities = new Municipality(),
        private readonly Quiz $quizzes = new Quiz(),
        private readonly PointOfInterest $points = new PointOfInterest(),
        private readonly MunicipalityMedia $media = new MunicipalityMedia()
    ) {
    }

    public function index(Request $request): void
    {
        $user = Auth::user();
        $homeStats = [
            'states' => 0,
            'municipalities' => 224,
            'publishedMunicipalities' => 0,
            'unpublishedMunicipalities' => 224,
            'mappedMunicipalities' => 0,
            'territorialCoveragePercent' => 0,
            'officialCoveragePercent' => 0,
            'missingGeoMunicipalities' => 0,
            'richMunicipalities' => 0,
            'quizzes' => 0,
        ];
        $editorialSummary = [
            'autoral' => 0,
            'em_curadoria' => 0,
            'base' => 0,
        ];
        $featuredMunicipalities = [];
        $featuredQuizzes = [];
        $featuredPoints = [];
        $featuredMedia = [];
        $favoriteMunicipalities = [];
        $visitedMunicipalities = [];
        $missingGeoMunicipalities = [];
        $databaseReady = true;

        try {
            $officialMunicipalities = $this->municipalities->officialCount('piaui');
            $publishedMunicipalities = $this->municipalities->publicCount('piaui');
            $mappedMunicipalities = $this->municipalities->publicWithCoordinatesCount('piaui');
            $missingGeoMunicipalities = $this->municipalities->publicMissingFromGeoJson('piaui');
            $homeStats = [
                'states' => count($this->states->all()),
                'municipalities' => $officialMunicipalities,
                'publishedMunicipalities' => $publishedMunicipalities,
                'unpublishedMunicipalities' => max(0, $officialMunicipalities - $publishedMunicipalities),
                'mappedMunicipalities' => $mappedMunicipalities,
                'missingGeoMunicipalities' => count($missingGeoMunicipalities),
                'quizzes' => $this->quizzes->publicCount(),
            ];
            $editorialSummary = $this->municipalities->editorialSummary('piaui');
            $homeStats['richMunicipalities'] = (int) ($editorialSummary['autoral'] ?? 0);
            $homeStats['territorialCoveragePercent'] = $homeStats['publishedMunicipalities'] > 0
                ? (int) floor(($homeStats['mappedMunicipalities'] / $homeStats['publishedMunicipalities']) * 100)
                : 0;
            $homeStats['officialCoveragePercent'] = $officialMunicipalities > 0
                ? (int) floor(($mappedMunicipalities / $officialMunicipalities) * 100)
                : 0;
            $featuredMunicipalities = $this->municipalities->featuredRichPublic(4);
            $featuredQuizzes = $this->quizzes->featuredPublic(3);
            $featuredPoints = $this->points->featuredPublic(3);
            $featuredMedia = $this->media->featuredPublic(10);

            if ($user !== null) {
                $favoriteMunicipalities = $this->municipalities->favoriteListForUser((int) $user['id']);
                $visitedMunicipalities = $this->municipalities->visitedListForUser((int) $user['id']);
            }
        } catch (Throwable $exception) {
            $databaseReady = false;
        }

        $this->view('home', [
            'title' => 'MAPI CONECTA',
            'user' => $user,
            'homeStats' => $homeStats,
            'featuredMunicipalities' => $featuredMunicipalities,
            'featuredQuizzes' => $featuredQuizzes,
            'featuredPoints' => $featuredPoints,
            'featuredMedia' => $featuredMedia,
            'favoriteMunicipalities' => $favoriteMunicipalities,
            'visitedMunicipalities' => $visitedMunicipalities,
            'editorialSummary' => $editorialSummary,
            'missingGeoMunicipalities' => $missingGeoMunicipalities,
            'databaseReady' => $databaseReady,
        ]);
    }
}
