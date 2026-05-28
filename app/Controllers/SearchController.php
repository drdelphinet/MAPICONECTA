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

final class SearchController extends Controller
{
    private const TYPE_ALL = 'todos';
    private const TYPE_MUNICIPALITIES = 'municipios';
    private const TYPE_POINTS = 'turismo';
    private const TYPE_ACTIVITIES = 'educacao';
    private const TYPE_MEDIA = 'midias';

    public function __construct(
        private readonly Municipality $municipalities = new Municipality(),
        private readonly PointOfInterest $points = new PointOfInterest(),
        private readonly EducationActivity $activities = new EducationActivity(),
        private readonly MunicipalityMedia $media = new MunicipalityMedia()
    ) {
    }

    public function index(Request $request): void
    {
        $search = trim((string) $request->input('q', ''));
        $selectedType = trim((string) $request->input('tipo', self::TYPE_ALL));
        $allowedTypes = [
            self::TYPE_ALL,
            self::TYPE_MUNICIPALITIES,
            self::TYPE_POINTS,
            self::TYPE_ACTIVITIES,
            self::TYPE_MEDIA,
        ];
        if (!in_array($selectedType, $allowedTypes, true)) {
            $selectedType = self::TYPE_ALL;
        }

        $results = [
            'municipalities' => [],
            'points' => [],
            'activities' => [],
            'media' => [],
        ];

        if ($search !== '') {
            $results['municipalities'] = $this->municipalities->searchPublic($search, 8);
            $results['points'] = $this->points->searchPublic($search, 6);
            $results['activities'] = $this->activities->searchPublic($search, 6);
            $results['media'] = $this->media->searchPublic($search, 6);
        }

        $counts = [
            self::TYPE_MUNICIPALITIES => count($results['municipalities']),
            self::TYPE_POINTS => count($results['points']),
            self::TYPE_ACTIVITIES => count($results['activities']),
            self::TYPE_MEDIA => count($results['media']),
        ];
        $totalResults = array_sum($counts);

        $visibleSections = [
            'municipalities' => $selectedType === self::TYPE_ALL || $selectedType === self::TYPE_MUNICIPALITIES,
            'points' => $selectedType === self::TYPE_ALL || $selectedType === self::TYPE_POINTS,
            'activities' => $selectedType === self::TYPE_ALL || $selectedType === self::TYPE_ACTIVITIES,
            'media' => $selectedType === self::TYPE_ALL || $selectedType === self::TYPE_MEDIA,
        ];

        $visibleResultCount = $selectedType === self::TYPE_ALL
            ? $totalResults
            : match ($selectedType) {
                self::TYPE_MUNICIPALITIES => $counts[self::TYPE_MUNICIPALITIES],
                self::TYPE_POINTS => $counts[self::TYPE_POINTS],
                self::TYPE_ACTIVITIES => $counts[self::TYPE_ACTIVITIES],
                self::TYPE_MEDIA => $counts[self::TYPE_MEDIA],
                default => $totalResults,
            };

        $this->view('search/index', [
            'title' => $search !== '' ? 'Busca por ' . $search : 'Busca publica',
            'user' => Auth::user(),
            'search' => $search,
            'selectedType' => $selectedType,
            'results' => $results,
            'counts' => $counts,
            'totalResults' => $totalResults,
            'visibleSections' => $visibleSections,
            'visibleResultCount' => $visibleResultCount,
        ]);
    }
}
