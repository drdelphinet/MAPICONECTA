<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Request;
use App\Models\EducationActivity;
use App\Models\Municipality;
use App\Models\MunicipalityMedia;
use App\Models\PointOfInterest;
use App\Models\Qrcode;
use App\Models\Quiz;
use App\Models\State;
use RuntimeException;

final class MunicipalityController extends Controller
{
    public function __construct(
        private readonly Municipality $municipalities = new Municipality(),
        private readonly State $states = new State(),
        private readonly Quiz $quizzes = new Quiz(),
        private readonly EducationActivity $activities = new EducationActivity(),
        private readonly PointOfInterest $points = new PointOfInterest(),
        private readonly MunicipalityMedia $media = new MunicipalityMedia(),
        private readonly Qrcode $qrcodes = new Qrcode()
    ) {
    }

    public function index(Request $request): void
    {
        $search = trim((string) $request->input('busca', ''));
        $stateSlug = trim((string) $request->input('estado', ''));
        $page = max(1, (int) $request->input('pagina', 1));
        $perPage = 24;
        $totalMunicipalities = $this->municipalities->publicListCount($search, $stateSlug !== '' ? $stateSlug : null);
        $officialMunicipalities = $search === '' && ($stateSlug === '' || $stateSlug === 'piaui')
            ? $this->municipalities->officialCount($stateSlug !== '' ? $stateSlug : 'piaui')
            : $totalMunicipalities;
        $totalPages = max(1, (int) ceil($totalMunicipalities / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;
        $user = Auth::user();
        $favoriteMunicipalityIds = [];
        $visitedMunicipalityIds = [];

        if ($user !== null) {
            $favoriteMunicipalityIds = array_map(
                static fn (array $municipality): int => (int) $municipality['id_municipio'],
                $this->municipalities->favoriteListForUser((int) $user['id'])
            );
            $visitedMunicipalityIds = array_map(
                static fn (array $municipality): int => (int) $municipality['id_municipio'],
                $this->municipalities->visitedListForUser((int) $user['id'])
            );
        }

        $this->view('municipalities/index', [
            'title' => 'Municipios',
            'user' => $user,
            'search' => $search,
            'currentPage' => $page,
            'perPage' => $perPage,
            'officialMunicipalities' => $officialMunicipalities,
            'totalMunicipalities' => $totalMunicipalities,
            'totalPages' => $totalPages,
            'selectedStateSlug' => $stateSlug,
            'states' => $this->states->all(),
            'municipalities' => $this->municipalities->publicListPaginated($search, $stateSlug !== '' ? $stateSlug : null, $perPage, $offset),
            'favoriteMunicipalityIds' => $favoriteMunicipalityIds,
            'visitedMunicipalityIds' => $visitedMunicipalityIds,
        ]);
    }

    public function show(Request $request): void
    {
        $municipality = $this->municipalities->findPublishedBySlug((string) $request->route('slug'));

        if ($municipality === null) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Municipio nao encontrado']);
            return;
        }

        $currentUser = Auth::user();
        $municipality = $this->municipalities->editorialCoverage($municipality);
        $municipality = $this->municipalities->factualCoverage($municipality);
        $points = $this->points->publicByMunicipality((int) $municipality['id_municipio']);
        $mediaItems = $this->media->publicByMunicipality((int) $municipality['id_municipio']);
        $visualGallery = $this->buildMunicipalityVisualGallery($municipality, $points, $mediaItems);
        $symbolGallery = array_values(array_filter(
            $visualGallery,
            static fn (array $item): bool => in_array((string) ($item['category'] ?? ''), ['bandeira', 'brasao'], true)
        ));
        $scenicGallery = array_values(array_filter(
            $visualGallery,
            static fn (array $item): bool => !in_array((string) ($item['category'] ?? ''), ['bandeira', 'brasao'], true)
        ));
        $mapPoints = $this->buildMunicipalityMapPoints($municipality, $points);
        $hasMapData = $mapPoints !== [];

        $this->view('municipalities/show', [
            'title' => $municipality['nome'],
            'user' => $currentUser,
            'municipality' => $municipality,
            'quizzes' => $this->quizzes->publicByMunicipality((int) $municipality['id_municipio']),
            'activities' => $this->activities->publicByMunicipality((int) $municipality['id_municipio']),
            'points' => $points,
            'mediaItems' => $mediaItems,
            'visualGallery' => $visualGallery,
            'symbolGallery' => $symbolGallery,
            'scenicGallery' => $scenicGallery,
            'mapPoints' => $mapPoints,
            'hasMapData' => $hasMapData,
            'publicUrl' => absolute_url('municipio/' . (string) $municipality['slug']),
            'qrTrackingUrl' => $this->qrcodes->trackingUrlForMunicipality($municipality),
            'qrImageUrl' => $this->qrcodes->imageUrl($this->qrcodes->trackingUrlForMunicipality($municipality), 280),
            'isFavorited' => $currentUser ? $this->municipalities->isFavoritedByUser((int) $municipality['id_municipio'], (int) $currentUser['id']) : false,
            'isVisited' => $currentUser ? $this->municipalities->isVisitedByUser((int) $municipality['id_municipio'], (int) $currentUser['id']) : false,
            'leafletStyles' => $hasMapData ? [
                'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
            ] : [],
            'pageScripts' => $hasMapData ? [
                'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
                asset('assets/js/municipality-show.js'),
            ] : [],
        ]);
    }

    public function map(Request $request): void
    {
        $stateSlug = trim((string) $request->input('estado', ''));
        $municipalitySlug = trim((string) $request->input('municipio', ''));
        $effectiveStateSlug = $stateSlug !== '' ? $stateSlug : 'piaui';
        $markers = $this->municipalities->publicForMap($stateSlug !== '' ? $stateSlug : null, $municipalitySlug !== '' ? $municipalitySlug : null);
        $publishedMunicipalities = $this->municipalities->publicCount($stateSlug !== '' ? $stateSlug : null);
        $officialMunicipalities = $this->municipalities->officialCount($effectiveStateSlug);
        $selectedMunicipality = $municipalitySlug !== '' ? $this->municipalities->findPublishedBySlug($municipalitySlug) : null;
        $selectedMunicipalityHasCoordinates = $selectedMunicipality !== null
            && $selectedMunicipality['latitude'] !== null
            && $selectedMunicipality['longitude'] !== null;
        $selectedMunicipalityHasGeoShape = $selectedMunicipality !== null
            && $this->municipalities->municipalityExistsInGeoJson((string) ($selectedMunicipality['codigo_ibge'] ?? ''));
        $geoMunicipalities = $this->municipalities->publicGeoEntities($stateSlug !== '' ? $stateSlug : null);
        $publishedWithoutCoordinates = $this->municipalities->publicWithoutCoordinates($stateSlug !== '' ? $stateSlug : null);
        $publishedWithoutCoordinatesCount = $this->municipalities->publicWithoutCoordinatesCount($stateSlug !== '' ? $stateSlug : null);
        $publishedMissingFromGeoJson = $this->municipalities->publicMissingFromGeoJson($stateSlug !== '' ? $stateSlug : null);
        $publishedMissingFromGeoJsonCount = count($publishedMissingFromGeoJson);
        $geoMunicipalityLookup = [];
        foreach ($geoMunicipalities as $municipality) {
            $code = preg_replace('/\D+/', '', (string) ($municipality['codigo_ibge'] ?? ''));
            if ($code !== '') {
                $geoMunicipalityLookup[$code] = $municipality;
            }
        }

        $this->view('municipalities/map', [
            'title' => 'Mapa interativo',
            'user' => Auth::user(),
            'states' => $this->states->all(),
            'municipalities' => $this->municipalities->publicOptions($stateSlug !== '' ? $stateSlug : null),
            'geoMunicipalities' => $geoMunicipalities,
            'geoMunicipalityLookup' => $geoMunicipalityLookup,
            'officialMunicipalities' => $officialMunicipalities,
            'publishedMunicipalities' => $publishedMunicipalities,
            'publishedWithoutCoordinates' => $publishedWithoutCoordinates,
            'publishedWithoutCoordinatesCount' => $publishedWithoutCoordinatesCount,
            'publishedMissingFromGeoJson' => $publishedMissingFromGeoJson,
            'publishedMissingFromGeoJsonCount' => $publishedMissingFromGeoJsonCount,
            'selectedMunicipality' => $selectedMunicipality,
            'selectedMunicipalityHasCoordinates' => $selectedMunicipalityHasCoordinates,
            'selectedMunicipalityHasGeoShape' => $selectedMunicipalityHasGeoShape,
            'selectedStateSlug' => $stateSlug,
            'selectedMunicipalitySlug' => $municipalitySlug,
            'markers' => $markers,
            'markerCoveragePercent' => $publishedMunicipalities > 0
                ? (int) floor((count($markers) / $publishedMunicipalities) * 100)
                : 0,
            'officialCoveragePercent' => $officialMunicipalities > 0
                ? (int) floor((count($markers) / $officialMunicipalities) * 100)
                : 0,
            'leafletStyles' => [
                'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
            ],
            'pageScripts' => [
                'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
                asset('assets/js/map-public.js'),
            ],
        ]);
    }

    public function markers(Request $request): void
    {
        $stateSlug = trim((string) $request->input('estado', ''));
        $municipalitySlug = trim((string) $request->input('municipio', ''));

        \App\Core\Response::json([
            'markers' => $this->municipalities->publicForMap($stateSlug !== '' ? $stateSlug : null, $municipalitySlug !== '' ? $municipalitySlug : null),
        ]);
    }

    public function toggleFavorite(Request $request): void
    {
        $municipality = $this->municipalities->findPublishedBySlug((string) $request->route('slug'));
        if ($municipality === null) {
            $this->redirectWithMessage('municipios', 'error', 'Municipio nao encontrado.');
        }

        try {
            Csrf::validate((string) $request->input('_token'));
        } catch (RuntimeException $exception) {
            $this->redirectWithMessage('municipio/' . $municipality['slug'], 'error', 'Token de seguranca invalido.');
        }

        $user = Auth::user();
        $active = $this->municipalities->toggleFavorite((int) $municipality['id_municipio'], (int) $user['id']);
        $message = $active ? 'Municipio adicionado aos favoritos.' : 'Municipio removido dos favoritos.';

        $this->redirectWithMessage('municipio/' . $municipality['slug'], 'success', $message);
    }

    public function toggleVisited(Request $request): void
    {
        $municipality = $this->municipalities->findPublishedBySlug((string) $request->route('slug'));
        if ($municipality === null) {
            $this->redirectWithMessage('municipios', 'error', 'Municipio nao encontrado.');
        }

        try {
            Csrf::validate((string) $request->input('_token'));
        } catch (RuntimeException $exception) {
            $this->redirectWithMessage('municipio/' . $municipality['slug'], 'error', 'Token de seguranca invalido.');
        }

        $user = Auth::user();
        $active = $this->municipalities->toggleVisited((int) $municipality['id_municipio'], (int) $user['id'], 'manual');
        $message = $active ? 'Municipio marcado como visitado.' : 'Municipio removido da lista de visitados.';

        $this->redirectWithMessage('municipio/' . $municipality['slug'], 'success', $message);
    }

    private function buildMunicipalityVisualGallery(array $municipality, array $points, array $mediaItems): array
    {
        $gallery = [];

        $mainImage = trim((string) ($municipality['imagem_principal'] ?? ''));
        if ($mainImage !== '') {
            $gallery[] = [
                'title' => $municipality['nome'],
                'description' => $municipality['descricao_curta'] ?: 'Imagem principal do municipio publicada no acervo.',
                'image_url' => $mainImage,
                'alt' => $municipality['nome'] . ' em destaque',
                'credit' => null,
                'label' => 'Paisagem do municipio',
                'category' => 'paisagem',
                'link_url' => absolute_url('municipio/' . (string) $municipality['slug']),
                'link_label' => 'Pagina do municipio',
            ];
        }

        foreach ($points as $point) {
            $imageUrl = trim((string) ($point['imagem'] ?? ''));
            if ($imageUrl === '') {
                continue;
            }

            $gallery[] = [
                'title' => (string) ($point['nome'] ?? 'Ponto turistico'),
                'description' => (string) (($point['descricao'] ?? '') ?: 'Imagem publicada de um atrativo local.'),
                'image_url' => $imageUrl,
                'alt' => (string) (($point['nome'] ?? 'Ponto turistico') . ' em ' . $municipality['nome']),
                'credit' => trim((string) ($point['fonte'] ?? '')) ?: null,
                'label' => 'Ponto turistico',
                'category' => 'atrativo',
                'link_url' => url('turismo/' . (string) $point['id_ponto_turistico']),
                'link_label' => 'Abrir atrativo',
            ];
        }

        foreach ($mediaItems as $item) {
            if (($item['tipo'] ?? '') !== 'imagem') {
                continue;
            }

            $imageUrl = trim((string) ($item['url_arquivo'] ?? ''));
            if ($imageUrl === '') {
                continue;
            }

            $category = trim((string) ($item['categoria_visual'] ?? ''));

            $gallery[] = [
                'title' => (string) ($item['titulo'] ?? 'Midia visual'),
                'description' => (string) (($item['descricao'] ?? '') ?: 'Imagem publicada para ampliar a leitura do municipio.'),
                'image_url' => $imageUrl,
                'alt' => (string) (($item['texto_alternativo'] ?? '') ?: ($item['titulo'] ?? 'Midia visual do municipio')),
                'credit' => trim((string) ($item['credito'] ?? '')) ?: null,
                'label' => $this->visualCategoryLabel($category, !empty($item['destaque'])),
                'category' => $category !== '' ? $category : (!empty($item['destaque']) ? 'paisagem' : 'documental'),
                'link_url' => url('midia/' . (string) $item['id_midia']),
                'link_label' => 'Abrir midia',
            ];
        }

        $unique = [];
        $filtered = [];
        foreach ($gallery as $item) {
            $key = md5((string) $item['image_url'] . '|' . (string) $item['title']);
            if (isset($unique[$key])) {
                continue;
            }

            $unique[$key] = true;
            $filtered[] = $item;
        }

        return array_slice($filtered, 0, 12);
    }

    private function visualCategoryLabel(string $category, bool $isHighlighted): string
    {
        return match ($category) {
            'bandeira' => 'Bandeira',
            'brasao' => 'Brasao',
            'patrimonio' => 'Patrimonio',
            'atrativo' => 'Atrativo',
            'cultura' => 'Cultura',
            'evento' => 'Evento',
            'educacao' => 'Educacao',
            'documental' => 'Documento visual',
            'paisagem' => 'Paisagem',
            default => $isHighlighted ? 'Midia em destaque' : 'Galeria do municipio',
        };
    }

    private function buildMunicipalityMapPoints(array $municipality, array $points): array
    {
        $items = [];
        $municipalityLat = $municipality['latitude'] ?? null;
        $municipalityLng = $municipality['longitude'] ?? null;

        if ($municipalityLat !== null && $municipalityLng !== null) {
            $items[] = [
                'type' => 'municipality',
                'name' => (string) $municipality['nome'],
                'description' => (string) (($municipality['descricao_curta'] ?? '') ?: 'Ponto de referencia principal do municipio.'),
                'latitude' => (float) $municipalityLat,
                'longitude' => (float) $municipalityLng,
                'link_url' => absolute_url('municipio/' . (string) $municipality['slug']),
                'link_label' => 'Pagina do municipio',
            ];
        }

        foreach ($points as $point) {
            if (($point['latitude'] ?? null) === null || ($point['longitude'] ?? null) === null) {
                continue;
            }

            $items[] = [
                'type' => 'point',
                'name' => (string) ($point['nome'] ?? 'Ponto turistico'),
                'description' => (string) (($point['descricao'] ?? '') ?: 'Atrativo publicado neste territorio.'),
                'latitude' => (float) $point['latitude'],
                'longitude' => (float) $point['longitude'],
                'link_url' => absolute_url('turismo/' . (string) $point['id_ponto_turistico']),
                'link_label' => 'Abrir atrativo',
            ];
        }

        return $items;
    }
}
