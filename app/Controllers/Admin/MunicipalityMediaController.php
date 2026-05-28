<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Request;
use App\Models\IntegrationLog;
use App\Models\Municipality;
use App\Models\MunicipalityMedia;
use App\Services\WikimediaCommonsService;
use RuntimeException;
use Throwable;

final class MunicipalityMediaController extends Controller
{
    private const MIN_AUTO_IMPORT_CONFIDENCE = 70;
    private const MAX_DISCOVERY_PREVIEW_LIMIT = 20;

    public function __construct(
        private readonly MunicipalityMedia $media = new MunicipalityMedia(),
        private readonly Municipality $municipalities = new Municipality(),
        private readonly WikimediaCommonsService $wikimedia = new WikimediaCommonsService(),
        private readonly IntegrationLog $logs = new IntegrationLog()
    ) {
    }

    public function index(Request $request): void
    {
        $coverageGaps = $this->municipalities->publishedWithoutVisualCoverage();
        $automationLogs = [];

        try {
            $automationLogs = $this->logs->latestBySource('Wikimedia Commons', 5, 'admin/midias/descobrir-lote/importar');
        } catch (Throwable) {
            $automationLogs = [];
        }

        $this->view('admin/media/index', [
            'title' => 'Galeria de midias',
            'user' => Auth::user(),
            'mediaItems' => $this->media->adminList(),
            'visualCategories' => MunicipalityMedia::visualCategories(),
            'coverageGapsCount' => count($coverageGaps),
            'automationLogsCount' => count($automationLogs),
        ], 'layouts/admin');
    }

    public function automationReport(Request $request): void
    {
        $logs = [];
        $error = null;

        try {
            $items = $this->logs->latestBySource('Wikimedia Commons', 30, 'admin/midias/descobrir-lote/importar');
            foreach ($items as $item) {
                $item['parametros_decodificados'] = $this->decodeLogJson((string) ($item['parametros'] ?? ''));
                $item['resumo_decodificado'] = $this->decodeLogJson((string) ($item['resposta_resumida'] ?? ''));
                $logs[] = $item;
            }
        } catch (Throwable) {
            $error = 'Os logs de automacao ainda nao estao disponiveis. Verifique se a tabela integracao_logs existe no banco.';
        }

        $this->view('admin/media/automation-report', [
            'title' => 'Automacao de imagens',
            'user' => Auth::user(),
            'logs' => $logs,
            'error' => $error,
        ], 'layouts/admin');
    }

    public function coverageReport(Request $request): void
    {
        $stateId = (int) $request->input('estado', 0);
        $coverageGaps = $this->municipalities->publishedWithoutVisualCoverage($stateId > 0 ? $stateId : null);
        $suggestedLines = [];
        $extendedLines = [];

        foreach ($coverageGaps as &$municipality) {
            $slug = (string) $municipality['slug'];
            $name = (string) $municipality['nome'];
            $token = str_replace('-', '_', slugify($name));
            $suggestedLines[] = $slug . ' | paisagem | File:' . $token . '_vista_geral.jpg | Vista de ' . $name;

            $extendedLines[] = '# ' . $name . ' (' . $municipality['estado_sigla'] . ')';
            $extendedLines[] = $slug . ' | paisagem | File:' . $token . '_vista_geral.jpg | Vista de ' . $name;
            $extendedLines[] = $slug . ' | bandeira | File:Bandeira_de_' . $token . '.svg | Bandeira de ' . $name;
            $extendedLines[] = $slug . ' | brasao | File:Brasao_de_' . $token . '.svg | Brasao de ' . $name;
            $extendedLines[] = '';

            $stateName = (string) ($municipality['estado_nome'] ?? '');
            $municipality['commons_search_url'] = $this->wikimedia->searchUrl($name . ' ' . $stateName);
            $municipality['commons_flag_search_url'] = $this->wikimedia->searchUrl('Bandeira de ' . $name);
            $municipality['commons_coat_search_url'] = $this->wikimedia->searchUrl('Brasao de ' . $name);
            $municipality['commons_category_url'] = $this->wikimedia->categoryUrl($name . ', ' . $stateName);
        }
        unset($municipality);

        $this->view('admin/media/coverage', [
            'title' => 'Cobertura visual dos municipios',
            'user' => Auth::user(),
            'coverageGaps' => $coverageGaps,
            'coverageGapCount' => count($coverageGaps),
            'states' => $this->statesForCoverageFilter(),
            'selectedStateId' => $stateId,
            'suggestedBatch' => trim(implode(PHP_EOL, $suggestedLines)),
            'extendedSuggestedBatch' => trim(implode(PHP_EOL, $extendedLines)),
        ], 'layouts/admin');
    }

    public function discoverCandidates(Request $request): void
    {
        $municipalityIdentifier = trim((string) $request->input('municipio', ''));
        $category = trim((string) $request->input('categoria', 'paisagem'));
        $municipality = $municipalityIdentifier !== '' ? $this->municipalities->findByIdentifier($municipalityIdentifier) : null;
        $query = '';
        $results = [];

        if ($municipality !== null) {
            $query = $this->buildDiscoveryQuery($municipality, $category);
            try {
                $results = $this->wikimedia->searchCandidates($query, 8);
                foreach ($results as &$result) {
                    $existing = $this->media->findExistingImport(
                        (int) $municipality['id_municipio'],
                        (string) ($result['file_url'] ?? ''),
                        (string) ($result['source_url'] ?? '')
                    );
                    $result['already_imported'] = $existing !== null;
                    $result['existing_media_id'] = $existing['id_midia'] ?? null;
                    $result['existing_status'] = $existing['status_curadoria'] ?? null;
                    $result['confidence_score'] = $this->confidenceScoreForCandidate($municipality, $category, $result);
                    $result['confidence_label'] = $this->confidenceLabel((int) $result['confidence_score']);
                }
                unset($result);
                usort($results, static fn (array $left, array $right): int => ((int) ($right['confidence_score'] ?? 0) <=> (int) ($left['confidence_score'] ?? 0)));
            } catch (RuntimeException $exception) {
                $this->redirectWithMessage('admin/midias/cobertura', 'error', $exception->getMessage());
            }
        }

        $this->view('admin/media/discover', [
            'title' => 'Descobrir imagens no Wikimedia Commons',
            'user' => Auth::user(),
            'municipalities' => $this->municipalities->adminList(),
            'selectedMunicipalityId' => (int) ($municipality['id_municipio'] ?? 0),
            'selectedCategory' => $category,
            'results' => $results,
            'query' => $query,
            'municipality' => $municipality,
            'visualCategories' => MunicipalityMedia::visualCategories(),
        ], 'layouts/admin');
    }

    public function discoverBatch(Request $request): void
    {
        $stateId = (int) $request->input('estado', 0);
        $category = trim((string) $request->input('categoria', 'paisagem'));
        $limit = max(1, min(self::MAX_DISCOVERY_PREVIEW_LIMIT, (int) $request->input('limite', 8)));
        $coverageGaps = $this->municipalities->publishedWithoutVisualCoverage($stateId > 0 ? $stateId : null);
        $coverageGaps = array_slice($coverageGaps, 0, $limit);
        $items = [];

        foreach ($coverageGaps as $municipality) {
            $query = $this->buildDiscoveryQuery($municipality, $category);
            $candidates = [];
            $error = null;
            $bestScore = 0;
            $autoSelectable = false;

            try {
                $candidates = $this->wikimedia->searchCandidates($query, 3);
                foreach ($candidates as &$candidate) {
                    $existing = $this->media->findExistingImport(
                        (int) $municipality['id_municipio'],
                        (string) ($candidate['file_url'] ?? ''),
                        (string) ($candidate['source_url'] ?? '')
                    );
                    $candidate['already_imported'] = $existing !== null;
                    $candidate['existing_media_id'] = $existing['id_midia'] ?? null;
                    $candidate['existing_status'] = $existing['status_curadoria'] ?? null;
                    $candidate['confidence_score'] = $this->confidenceScoreForCandidate($municipality, $category, $candidate);
                    $candidate['confidence_label'] = $this->confidenceLabel((int) $candidate['confidence_score']);
                    $bestScore = max($bestScore, (int) $candidate['confidence_score']);
                }
                unset($candidate);
                usort($candidates, static fn (array $left, array $right): int => ((int) ($right['confidence_score'] ?? 0) <=> (int) ($left['confidence_score'] ?? 0)));
                $autoSelectable = $bestScore >= self::MIN_AUTO_IMPORT_CONFIDENCE;
            } catch (RuntimeException $exception) {
                $error = $exception->getMessage();
            }

            $items[] = [
                'municipality' => $municipality,
                'query' => $query,
                'candidates' => $candidates,
                'error' => $error,
                'best_score' => $bestScore,
                'auto_selectable' => $autoSelectable,
            ];
        }

        $this->view('admin/media/discover-batch', [
            'title' => 'Descoberta em lote no Wikimedia Commons',
            'user' => Auth::user(),
            'states' => $this->statesForCoverageFilter(),
            'selectedStateId' => $stateId,
            'selectedCategory' => $category,
            'selectedLimit' => $limit,
            'items' => $items,
        ], 'layouts/admin');
    }

    public function importDiscoveryBatch(Request $request): void
    {
        try {
            Csrf::validate((string) $request->input('_token'));
        } catch (RuntimeException $exception) {
            $this->redirectWithMessage('admin/midias/descobrir-lote', 'error', 'Token de seguranca invalido.');
        }

        $stateId = (int) $request->input('estado', 0);
        $category = trim((string) $request->input('categoria', 'paisagem'));
        $requestedLimit = (int) $request->input('limite', 0);
        $limit = $requestedLimit > 0 ? $requestedLimit : 0;
        $status = $this->resolveImportStatus((string) $request->input('status_curadoria', 'rascunho'));
        $destaque = (int) $request->input('destaque', 0) === 1;
        $fillMainImage = (int) $request->input('preencher_imagem_principal', 1) === 1;
        $selectedMunicipalities = array_values(array_filter(
            array_map(
                static fn (mixed $value): int => (int) $value,
                (array) $request->input('municipios', [])
            ),
            static fn (int $value): bool => $value > 0
        ));
        $user = Auth::user();
        $coverageGaps = $this->municipalities->publishedWithoutVisualCoverage($stateId > 0 ? $stateId : null);
        if ($limit > 0) {
            $coverageGaps = array_slice($coverageGaps, 0, $limit);
        }

        if ($selectedMunicipalities !== []) {
            $selectedLookup = array_fill_keys($selectedMunicipalities, true);
            $coverageGaps = array_values(array_filter(
                $coverageGaps,
                static fn (array $municipality): bool => isset($selectedLookup[(int) ($municipality['id_municipio'] ?? 0)])
            ));
        }

        if ($coverageGaps === []) {
            $this->redirectWithMessage('admin/midias/descobrir-lote', 'warning', 'Nenhum municipio foi selecionado para importacao automatica.');
        }

        $imported = 0;
        $skipped = 0;
        $promoted = 0;
        $errors = [];

        foreach ($coverageGaps as $municipality) {
            try {
                $query = $this->buildDiscoveryQuery($municipality, $category);
                $candidates = $this->wikimedia->searchCandidates($query, 5);
                $selected = null;

                foreach ($candidates as $candidate) {
                    $score = $this->confidenceScoreForCandidate($municipality, $category, $candidate);
                    if ($score < self::MIN_AUTO_IMPORT_CONFIDENCE) {
                        continue;
                    }
                    $existing = $this->media->findExistingImport(
                        (int) $municipality['id_municipio'],
                        (string) ($candidate['file_url'] ?? ''),
                        (string) ($candidate['source_url'] ?? '')
                    );
                    if ($existing === null) {
                        $selected = $candidate;
                        break;
                    }
                }

                if ($selected === null) {
                    $skipped++;
                    continue;
                }

                $metadata = $this->wikimedia->fetchFileMetadata((string) ($selected['canonical_title'] ?? ''));
                $createdId = $this->media->create($this->buildImportedPayload(
                    $metadata,
                    (int) $municipality['id_municipio'],
                    [
                        'titulo' => (string) ($selected['title'] ?? ''),
                        'categoria_visual' => $category,
                        'destaque' => $destaque,
                        'status_curadoria' => $status,
                    ],
                    $user['id'] ?? null
                ));

                if ($createdId === 0) {
                    $this->redirectWithMessage('admin/midias', 'warning', 'A tabela de midias ainda nao existe no banco. Atualize o schema antes de importar.');
                }

                if (
                    $fillMainImage
                    && $this->shouldPromoteImportedImageAsMain($category)
                    && $this->municipalities->promoteMainImageIfEmpty(
                        (int) $municipality['id_municipio'],
                        (string) ($metadata['file_url'] ?? ''),
                        $user['id'] ?? null
                    )
                ) {
                    $promoted++;
                }

                $imported++;
            } catch (RuntimeException $exception) {
                $errors[] = $municipality['nome'] . ': ' . $exception->getMessage();
            }
        }

        if ($imported === 0 && $errors !== []) {
            $this->logDiscoveryBatchImport($stateId, $category, $limit, $selectedMunicipalities, $imported, $skipped, $promoted, $errors, 'erro');
            $this->redirectWithMessage('admin/midias/descobrir-lote', 'error', implode(' | ', array_slice($errors, 0, 3)));
        }

        if ($errors !== []) {
            $this->logDiscoveryBatchImport($stateId, $category, $limit, $selectedMunicipalities, $imported, $skipped, $promoted, $errors, 'parcial');
            $message = sprintf(
                '%d midias importadas automaticamente, %d municipios sem candidato livre e %d erro(s): %s',
                $imported,
                $skipped,
                count($errors),
                implode(' | ', array_slice($errors, 0, 2))
            );
            $this->redirectWithMessage('admin/midias', 'warning', $message);
        }

        $this->logDiscoveryBatchImport($stateId, $category, $limit, $selectedMunicipalities, $imported, $skipped, $promoted, [], 'sucesso');
        $message = sprintf(
            '%d midias importadas automaticamente. %d municipio(s) ja estavam sem candidato livre e %d capa(s) foram preenchidas.',
            $imported,
            $skipped,
            $promoted
        );

        $this->redirectWithMessage('admin/midias', 'success', $message);
    }

    public function create(Request $request): void
    {
        $this->view('admin/media/form', [
            'title' => 'Nova midia',
            'user' => Auth::user(),
            'mediaItem' => null,
            'municipalities' => $this->municipalities->adminList(),
            'formAction' => url('admin/midias'),
            'visualCategories' => MunicipalityMedia::visualCategories(),
        ], 'layouts/admin');
    }

    public function importForm(Request $request): void
    {
        $prefillSource = trim((string) $request->input('source', ''));
        $prefillTitle = trim((string) $request->input('titulo', ''));
        $prefillCategory = trim((string) $request->input('categoria', ''));
        $prefillMunicipality = trim((string) $request->input('municipio', ''));
        $selectedMunicipalityId = 0;

        if ($prefillMunicipality !== '') {
            $municipality = $this->municipalities->findByIdentifier($prefillMunicipality);
            $selectedMunicipalityId = (int) ($municipality['id_municipio'] ?? 0);
        }

        $this->view('admin/media/import', [
            'title' => 'Importar do Wikimedia Commons',
            'user' => Auth::user(),
            'municipalities' => $this->municipalities->adminList(),
            'importDraft' => null,
            'visualCategories' => MunicipalityMedia::visualCategories(),
            'prefillSource' => $prefillSource,
            'prefillTitle' => $prefillTitle,
            'prefillCategory' => $prefillCategory,
            'selectedMunicipalityId' => $selectedMunicipalityId,
        ], 'layouts/admin');
    }

    public function bulkImportForm(Request $request): void
    {
        $prefill = trim((string) $request->input('prefill', ''));
        $prefillMunicipality = trim((string) $request->input('municipio', ''));
        $selectedMunicipalityId = 0;

        if ($prefillMunicipality !== '') {
            $municipality = $this->municipalities->findByIdentifier($prefillMunicipality);
            $selectedMunicipalityId = (int) ($municipality['id_municipio'] ?? 0);
        }

        $this->view('admin/media/import-bulk', [
            'title' => 'Importar lote do Wikimedia Commons',
            'user' => Auth::user(),
            'municipalities' => $this->municipalities->adminList(),
            'visualCategories' => MunicipalityMedia::visualCategories(),
            'prefillSources' => $prefill,
            'selectedMunicipalityId' => $selectedMunicipalityId,
        ], 'layouts/admin');
    }

    public function importFromWikimedia(Request $request): void
    {
        try {
            Csrf::validate((string) $request->input('_token'));
        } catch (RuntimeException $exception) {
            $this->redirectWithMessage('admin/midias/importar-wikimedia', 'error', 'Token de seguranca invalido.');
        }

        $municipalityId = (int) $request->input('id_municipio');
        $source = trim((string) $request->input('wikimedia_source'));
        $status = $this->resolveImportStatus((string) $request->input('status_curadoria', 'rascunho'));
        $destaque = (int) $request->input('destaque', 0) === 1;
        $user = Auth::user();

        try {
            $metadata = $this->wikimedia->fetchFileMetadata($source);
        } catch (RuntimeException $exception) {
            $this->redirectWithMessage('admin/midias/importar-wikimedia', 'error', $exception->getMessage());
        }

        $payload = $this->buildImportedPayload(
            $metadata,
            $municipalityId,
            [
                'titulo' => trim((string) $request->input('titulo', '')),
                'descricao' => trim((string) $request->input('descricao', '')),
                'credito' => trim((string) $request->input('credito', '')),
                'texto_alternativo' => trim((string) $request->input('texto_alternativo', '')),
                'licenca' => trim((string) $request->input('licenca', '')),
                'url_origem' => trim((string) $request->input('url_origem', '')),
                'categoria_visual' => trim((string) $request->input('categoria_visual', '')),
                'destaque' => $destaque,
                'status_curadoria' => $status,
            ],
            $user['id'] ?? null
        );

        $createdId = $this->media->create($payload);
        if ($createdId === 0) {
            $this->redirectWithMessage('admin/midias', 'warning', 'A tabela de midias ainda nao existe no banco. Atualize o schema antes de importar.');
        }

        $this->redirectWithMessage('admin/midias', 'success', 'Midia importada do Wikimedia Commons com sucesso.');
    }

    public function bulkImportFromWikimedia(Request $request): void
    {
        try {
            Csrf::validate((string) $request->input('_token'));
        } catch (RuntimeException $exception) {
            $this->redirectWithMessage('admin/midias/importar-lote-wikimedia', 'error', 'Token de seguranca invalido.');
        }

        $municipalityId = (int) $request->input('id_municipio');
        $status = $this->resolveImportStatus((string) $request->input('status_curadoria', 'rascunho'));
        $defaultCategory = trim((string) $request->input('categoria_visual', ''));
        $destaque = (int) $request->input('destaque', 0) === 1;
        $fillMainImage = (int) $request->input('preencher_imagem_principal', 1) === 1;
        $rawSources = trim((string) $request->input('wikimedia_sources'));
        $user = Auth::user();

        if ($rawSources === '') {
            $this->redirectWithMessage('admin/midias/importar-lote-wikimedia', 'error', 'Informe ao menos uma linha para importar.');
        }

        $lines = preg_split('/\r\n|\r|\n/', $rawSources) ?: [];
        $imported = 0;
        $skipped = 0;
        $promoted = 0;
        $errors = [];

        foreach ($lines as $index => $line) {
            try {
                $parsed = $this->parseBulkImportLine($line, $defaultCategory, $municipalityId);
                if ($parsed === null) {
                    continue;
                }

                $resolvedMunicipalityId = $parsed['municipality_id'];
                if ($resolvedMunicipalityId <= 0) {
                    throw new RuntimeException('Municipio nao identificado para a linha.');
                }

                $metadata = $this->wikimedia->fetchFileMetadata($parsed['source']);
                $existing = $this->media->findExistingImport(
                    $resolvedMunicipalityId,
                    (string) ($metadata['file_url'] ?? ''),
                    (string) ($metadata['source_url'] ?? '')
                );

                if ($existing !== null) {
                    $skipped++;
                    continue;
                }

                $createdId = $this->media->create($this->buildImportedPayload(
                    $metadata,
                    $resolvedMunicipalityId,
                    [
                        'titulo' => $parsed['title'],
                        'categoria_visual' => $parsed['category'],
                        'destaque' => $destaque,
                        'status_curadoria' => $status,
                    ],
                    $user['id'] ?? null
                ));

                if ($createdId === 0) {
                    $this->redirectWithMessage('admin/midias', 'warning', 'A tabela de midias ainda nao existe no banco. Atualize o schema antes de importar.');
                }

                if (
                    $fillMainImage
                    && $this->shouldPromoteImportedImageAsMain($parsed['category'])
                    && $this->municipalities->promoteMainImageIfEmpty(
                        $resolvedMunicipalityId,
                        (string) ($metadata['file_url'] ?? ''),
                        $user['id'] ?? null
                    )
                ) {
                    $promoted++;
                }

                $imported++;
            } catch (RuntimeException $exception) {
                $errors[] = 'Linha ' . ($index + 1) . ': ' . $exception->getMessage();
            }
        }

        if ($imported === 0 && $errors !== []) {
            $this->redirectWithMessage('admin/midias/importar-lote-wikimedia', 'error', implode(' | ', array_slice($errors, 0, 3)));
        }

        if ($errors !== []) {
            $message = sprintf(
                '%d midias importadas, %d duplicadas ignoradas e %d linhas precisaram de revisao: %s',
                $imported,
                $skipped,
                count($errors),
                implode(' | ', array_slice($errors, 0, 2))
            );
            $this->redirectWithMessage('admin/midias', 'warning', $message);
        }

        $message = sprintf(
            '%d midias importadas do Wikimedia Commons com sucesso. %d duplicadas foram ignoradas e %d municipio(s) receberam imagem principal.',
            $imported,
            $skipped,
            $promoted
        );

        $this->redirectWithMessage('admin/midias', 'success', $message);
    }

    public function store(Request $request): void
    {
        $user = Auth::user();
        $createdId = $this->media->create($this->validate($request, $user['id'] ?? null));
        if ($createdId === 0) {
            $this->redirectWithMessage('admin/midias', 'warning', 'A tabela de midias ainda nao existe no banco. Atualize o schema antes de cadastrar.');
        }

        $this->redirectWithMessage('admin/midias', 'success', 'Midia cadastrada com sucesso.');
    }

    public function edit(Request $request): void
    {
        $mediaItem = $this->media->find((int) $request->route('id'));
        if ($mediaItem === null) {
            $this->redirectWithMessage('admin/midias', 'error', 'Midia nao encontrada.');
        }

        $this->view('admin/media/form', [
            'title' => 'Editar midia',
            'user' => Auth::user(),
            'mediaItem' => $mediaItem,
            'municipalities' => $this->municipalities->adminList(),
            'formAction' => url('admin/midias/' . $mediaItem['id_midia']),
            'visualCategories' => MunicipalityMedia::visualCategories(),
        ], 'layouts/admin');
    }

    public function update(Request $request): void
    {
        $mediaItem = $this->media->find((int) $request->route('id'));
        if ($mediaItem === null) {
            $this->redirectWithMessage('admin/midias', 'error', 'Midia nao encontrada.');
        }

        $user = Auth::user();
        $this->media->update((int) $mediaItem['id_midia'], $this->validate($request, $user['id'] ?? null, $mediaItem));

        $this->redirectWithMessage('admin/midias', 'success', 'Midia atualizada com sucesso.');
    }

    private function validate(Request $request, ?int $userId, ?array $current = null): array
    {
        try {
            Csrf::validate((string) $request->input('_token'));
        } catch (RuntimeException $exception) {
            $this->redirectWithMessage('admin/midias', 'error', 'Token de seguranca invalido.');
        }

        $role = Auth::user()['role_slug'] ?? null;
        $status = trim((string) $request->input('status_curadoria', 'rascunho'));

        if ($role === 'editor' && !in_array($status, ['rascunho', 'enviado_revisao', 'em_correcao'], true)) {
            $status = $current['status_curadoria'] ?? 'rascunho';
        }

        return [
            'id_municipio' => (int) $request->input('id_municipio'),
            'titulo' => trim((string) $request->input('titulo')),
            'descricao' => trim((string) $request->input('descricao')),
            'tipo' => trim((string) $request->input('tipo', 'imagem')),
            'url_arquivo' => trim((string) $request->input('url_arquivo')),
            'credito' => trim((string) $request->input('credito')),
            'texto_alternativo' => trim((string) $request->input('texto_alternativo')),
            'licenca' => trim((string) $request->input('licenca')),
            'url_origem' => trim((string) $request->input('url_origem')),
            'categoria_visual' => trim((string) $request->input('categoria_visual')),
            'destaque' => (int) $request->input('destaque', 0) === 1,
            'status_curadoria' => $status,
            'criado_por' => $current['criado_por'] ?? $userId,
            'revisado_por' => in_array($status, ['aprovado', 'publicado', 'reprovado'], true) ? $userId : ($current['revisado_por'] ?? null),
            'publicado_por' => $status === 'publicado' ? $userId : ($current['publicado_por'] ?? null),
        ];
    }

    private function resolveImportStatus(string $status): string
    {
        $status = trim($status);
        $role = Auth::user()['role_slug'] ?? null;

        if ($role === 'editor' && !in_array($status, ['rascunho', 'enviado_revisao', 'em_correcao'], true)) {
            return 'rascunho';
        }

        return $status !== '' ? $status : 'rascunho';
    }

    private function buildImportedPayload(array $metadata, int $municipalityId, array $overrides, ?int $userId): array
    {
        $status = (string) ($overrides['status_curadoria'] ?? 'rascunho');

        return [
            'id_municipio' => $municipalityId,
            'titulo' => trim((string) ($overrides['titulo'] ?? '')) ?: trim((string) ($metadata['title'] ?? '')),
            'descricao' => trim((string) ($overrides['descricao'] ?? '')) ?: trim((string) ($metadata['description'] ?? '')),
            'tipo' => 'imagem',
            'url_arquivo' => (string) ($metadata['file_url'] ?? ''),
            'credito' => trim((string) ($overrides['credito'] ?? '')) ?: trim((string) ($metadata['credit'] ?? '')),
            'texto_alternativo' => trim((string) ($overrides['texto_alternativo'] ?? '')) ?: trim((string) ($metadata['alt_text'] ?? '')),
            'licenca' => trim((string) ($overrides['licenca'] ?? '')) ?: trim((string) ($metadata['license'] ?? '')),
            'url_origem' => trim((string) ($overrides['url_origem'] ?? '')) ?: trim((string) ($metadata['source_url'] ?? '')),
            'categoria_visual' => trim((string) ($overrides['categoria_visual'] ?? '')),
            'destaque' => !empty($overrides['destaque']),
            'status_curadoria' => $status,
            'criado_por' => $userId,
            'revisado_por' => in_array($status, ['aprovado', 'publicado', 'reprovado'], true) ? $userId : null,
            'publicado_por' => $status === 'publicado' ? $userId : null,
        ];
    }

    private function parseBulkImportLine(string $line, string $defaultCategory, int $defaultMunicipalityId = 0): ?array
    {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            return null;
        }

        $parts = array_map(static fn (string $value): string => trim($value), explode('|', $line));

        if ($defaultMunicipalityId > 0) {
            if (count($parts) === 1) {
                return [
                    'municipality_id' => $defaultMunicipalityId,
                    'category' => $defaultCategory,
                    'source' => $parts[0],
                    'title' => '',
                ];
            }

            if (count($parts) === 2) {
                return [
                    'municipality_id' => $defaultMunicipalityId,
                    'category' => $defaultCategory,
                    'source' => $parts[0],
                    'title' => $parts[1],
                ];
            }

            return [
                'municipality_id' => $defaultMunicipalityId,
                'category' => $parts[0] !== '' ? $parts[0] : $defaultCategory,
                'source' => $parts[1] ?? '',
                'title' => $parts[2] ?? '',
            ];
        }

        if (count($parts) < 2) {
            throw new RuntimeException('Use municipio | arquivo ou municipio | categoria | arquivo | titulo quando nenhum municipio estiver selecionado.');
        }

        $municipality = $this->municipalities->findByIdentifier($parts[0]);
        if ($municipality === null) {
            throw new RuntimeException('Municipio da linha nao foi encontrado: ' . $parts[0]);
        }

        if (count($parts) === 2) {
            return [
                'municipality_id' => (int) $municipality['id_municipio'],
                'category' => $defaultCategory,
                'source' => $parts[1],
                'title' => '',
            ];
        }

        if (count($parts) === 3) {
            return [
                'municipality_id' => (int) $municipality['id_municipio'],
                'category' => $defaultCategory,
                'source' => $parts[1],
                'title' => $parts[2],
            ];
        }

        return [
            'municipality_id' => (int) $municipality['id_municipio'],
            'category' => $parts[1] !== '' ? $parts[1] : $defaultCategory,
            'source' => $parts[2],
            'title' => $parts[3] ?? '',
        ];
    }

    private function shouldPromoteImportedImageAsMain(string $category): bool
    {
        return !in_array(trim($category), ['bandeira', 'brasao'], true);
    }

    private function statesForCoverageFilter(): array
    {
        $items = $this->municipalities->adminList();
        $states = [];

        foreach ($items as $municipality) {
            $stateId = (int) ($municipality['id_estado'] ?? 0);
            if ($stateId <= 0 || isset($states[$stateId])) {
                continue;
            }

            $states[$stateId] = [
                'id_estado' => $stateId,
                'estado_nome' => (string) ($municipality['estado_nome'] ?? ''),
                'estado_sigla' => (string) ($municipality['estado_sigla'] ?? ''),
            ];
        }

        return array_values($states);
    }

    private function buildDiscoveryQuery(array $municipality, string $category): string
    {
        $name = (string) ($municipality['nome'] ?? '');
        $stateName = (string) ($municipality['estado_nome'] ?? '');

        return match ($category) {
            'bandeira' => 'Bandeira de ' . $name,
            'brasao' => 'Brasao de ' . $name,
            'patrimonio' => $name . ' patrimonio ' . $stateName,
            'atrativo' => $name . ' turismo ' . $stateName,
            default => $name . ' ' . $stateName,
        };
    }

    private function confidenceScoreForCandidate(array $municipality, string $category, array $candidate): int
    {
        $name = $this->normalizeSearchText((string) ($municipality['nome'] ?? ''));
        $nameTokens = array_values(array_filter(explode(' ', $name), static fn (string $token): bool => mb_strlen($token) >= 3));
        $stateName = $this->normalizeSearchText((string) ($municipality['estado_nome'] ?? ''));
        $title = $this->normalizeSearchText((string) ($candidate['title'] ?? ''));
        $description = $this->normalizeSearchText((string) ($candidate['description'] ?? ''));
        $sourceUrl = $this->normalizeSearchText((string) ($candidate['source_url'] ?? ''));
        $score = 0;

        if ($name !== '' && str_contains($title, $name)) {
            $score += 45;
        }

        if ($name !== '' && str_contains($description, $name)) {
            $score += 18;
        }

        if ($nameTokens !== []) {
            $matchedTokens = 0;
            foreach ($nameTokens as $token) {
                if (str_contains($title, $token) || str_contains($description, $token) || str_contains($sourceUrl, $token)) {
                    $matchedTokens++;
                }
            }

            if ($matchedTokens > 0) {
                $score += min(20, $matchedTokens * 6);
            }
        }

        if ($stateName !== '' && (str_contains($title, $stateName) || str_contains($description, $stateName) || str_contains($sourceUrl, $stateName))) {
            $score += 10;
        }

        $categoryTokens = match ($category) {
            'bandeira' => ['bandeira', 'flag'],
            'brasao' => ['brasao', 'brasão', 'coat of arms', 'escudo'],
            'patrimonio' => ['patrimonio', 'patrimônio', 'church', 'cathedral', 'historic'],
            'atrativo' => ['turismo', 'tourism', 'parque', 'praia', 'cachoeira', 'atrativo'],
            default => ['vista', 'panorama', 'city', 'municipio', 'município'],
        };

        foreach ($categoryTokens as $token) {
            if (str_contains($title, $token) || str_contains($description, $token) || str_contains($sourceUrl, str_replace(' ', '_', $token))) {
                $score += 10;
                break;
            }
        }

        $normalizedCategoryTokens = match ($category) {
            'bandeira' => ['bandeira', 'flag'],
            'brasao' => ['brasao', 'coat of arms', 'escudo'],
            'patrimonio' => ['patrimonio', 'church', 'cathedral', 'historic', 'igreja', 'historic center'],
            'atrativo' => ['turismo', 'tourism', 'parque', 'praia', 'cachoeira', 'atrativo'],
            default => ['vista', 'panorama', 'city', 'municipio', 'skyline', 'downtown'],
        };

        foreach ($normalizedCategoryTokens as $token) {
            $normalizedToken = $this->normalizeSearchText($token);
            if (
                $normalizedToken !== ''
                && (
                    str_contains($title, $normalizedToken)
                    || str_contains($description, $normalizedToken)
                    || str_contains($sourceUrl, str_replace(' ', '-', $normalizedToken))
                )
            ) {
                $score += 8;
                break;
            }
        }

        if (!empty($candidate['already_imported'])) {
            $score -= 100;
        }

        return max(0, min(100, $score));
    }

    private function confidenceLabel(int $score): string
    {
        return match (true) {
            $score >= 85 => 'Alta',
            $score >= self::MIN_AUTO_IMPORT_CONFIDENCE => 'Boa',
            $score >= 45 => 'Media',
            default => 'Baixa',
        };
    }

    private function normalizeSearchText(string $value): string
    {
        $normalized = str_replace('-', ' ', slugify($value));

        return trim(preg_replace('/\s+/', ' ', $normalized) ?? '');
    }

    private function decodeLogJson(string $value): array
    {
        if (trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function logDiscoveryBatchImport(
        int $stateId,
        string $category,
        int $limit,
        array $selectedMunicipalities,
        int $imported,
        int $skipped,
        int $promoted,
        array $errors,
        string $status
    ): void {
        try {
            $this->logs->create([
                'fonte' => 'Wikimedia Commons',
                'endpoint' => 'admin/midias/descobrir-lote/importar',
                'parametros' => [
                    'estado' => $stateId > 0 ? $stateId : null,
                    'categoria' => $category,
                    'limite' => $limit,
                    'municipios' => $selectedMunicipalities,
                    'confianca_minima' => self::MIN_AUTO_IMPORT_CONFIDENCE,
                ],
                'status' => $status,
                'mensagem' => $errors === []
                    ? 'Importacao automatica de candidatos do Commons concluida.'
                    : 'Importacao automatica de candidatos do Commons concluida com restricoes.',
                'resposta_resumida' => json_encode([
                    'importadas' => $imported,
                    'puladas_sem_candidato' => $skipped,
                    'capas_preenchidas' => $promoted,
                    'erros' => array_slice($errors, 0, 10),
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'usuario_id' => Auth::user()['id'] ?? null,
            ]);
        } catch (Throwable) {
            // O log e auxiliar; a importacao nao deve falhar se a tabela ainda nao existir.
        }
    }
}
