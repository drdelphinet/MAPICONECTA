<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Municipality;
use App\Models\MunicipalityMedia;
use App\Models\State;
use RuntimeException;

final class WikimediaBulkImporterService
{
    private const DEFAULT_CATEGORIES = ['paisagem', 'bandeira', 'brasao', 'patrimonio', 'atrativo'];

    private array $lastReport = [];

    public function __construct(
        private readonly Municipality $municipalities = new Municipality(),
        private readonly MunicipalityMedia $media = new MunicipalityMedia(),
        private readonly WikimediaCommonsService $wikimedia = new WikimediaCommonsService(),
        private readonly State $states = new State()
    ) {
    }

    public function importState(string $stateCodeOrSigla, ?int $userId = null, array $options = []): int
    {
        $state = $this->states->findByCodeOrSigla($stateCodeOrSigla);
        if ($state === null) {
            throw new RuntimeException('Estado nao encontrado para importacao Wikimedia.');
        }

        $categories = array_values(array_filter(
            array_map(static fn (mixed $value): string => trim((string) $value), (array) ($options['categories'] ?? self::DEFAULT_CATEGORIES)),
            static fn (string $value): bool => $value !== ''
        ));
        if ($categories === []) {
            $categories = self::DEFAULT_CATEGORIES;
        }

        $limit = max(0, (int) ($options['limit'] ?? 0));
        $onlyMissingCoverage = (bool) ($options['only_missing_coverage'] ?? true);
        $status = trim((string) ($options['status_curadoria'] ?? 'rascunho')) ?: 'rascunho';
        $minScore = (int) ($options['min_score'] ?? 0);
        $destaque = (bool) ($options['destaque'] ?? false);
        $fillMainImage = (bool) ($options['preencher_imagem_principal'] ?? true);
        $searchLimit = max(1, min(10, (int) ($options['search_limit'] ?? 5)));
        $progress = $options['progress'] ?? null;

        $municipalities = $onlyMissingCoverage
            ? $this->municipalities->publishedWithoutVisualCoverage((int) $state['id_estado'])
            : $this->municipalities->adminList((int) $state['id_estado']);

        if ($limit > 0) {
            $municipalities = array_slice($municipalities, 0, $limit);
        }

        $report = [
            'state' => $state,
            'processed' => 0,
            'municipalities_with_imports' => 0,
            'imported' => 0,
            'skipped_duplicates' => 0,
            'skipped_without_candidate' => 0,
            'promoted' => 0,
            'failed' => 0,
            'failures' => [],
            'categories' => $categories,
        ];

        foreach ($municipalities as $municipality) {
            $report['processed']++;
            $importsForMunicipality = 0;

            foreach ($categories as $category) {
                try {
                    $candidate = $this->selectCandidate($municipality, $category, $minScore, $searchLimit);
                    if ($candidate === null) {
                        $report['skipped_without_candidate']++;
                        continue;
                    }

                    $metadata = $this->wikimedia->fetchFileMetadata((string) ($candidate['canonical_title'] ?? ''));
                    $existing = $this->media->findExistingImport(
                        (int) $municipality['id_municipio'],
                        (string) ($metadata['file_url'] ?? ''),
                        (string) ($metadata['source_url'] ?? '')
                    );

                    if ($existing !== null) {
                        $report['skipped_duplicates']++;
                        continue;
                    }

                    $createdId = $this->media->create([
                        'id_municipio' => (int) $municipality['id_municipio'],
                        'titulo' => trim((string) ($candidate['title'] ?? '')) ?: trim((string) ($metadata['title'] ?? '')),
                        'descricao' => trim((string) ($metadata['description'] ?? '')),
                        'tipo' => 'imagem',
                        'url_arquivo' => (string) ($metadata['file_url'] ?? ''),
                        'credito' => trim((string) ($metadata['credit'] ?? '')),
                        'texto_alternativo' => trim((string) ($metadata['alt_text'] ?? '')),
                        'licenca' => trim((string) ($metadata['license'] ?? '')),
                        'url_origem' => trim((string) ($metadata['source_url'] ?? '')),
                        'categoria_visual' => $category,
                        'destaque' => $destaque,
                        'status_curadoria' => $status,
                        'criado_por' => $userId,
                        'revisado_por' => in_array($status, ['aprovado', 'publicado', 'reprovado'], true) ? $userId : null,
                        'publicado_por' => $status === 'publicado' ? $userId : null,
                    ]);

                    if ($createdId === 0) {
                        throw new RuntimeException('Tabela de midias nao encontrada no banco.');
                    }

                    if (
                        $fillMainImage
                        && $category !== 'bandeira'
                        && $category !== 'brasao'
                        && $this->municipalities->promoteMainImageIfEmpty(
                            (int) $municipality['id_municipio'],
                            (string) ($metadata['file_url'] ?? ''),
                            $userId
                        )
                    ) {
                        $report['promoted']++;
                    }

                    $report['imported']++;
                    $importsForMunicipality++;
                } catch (RuntimeException $exception) {
                    $report['failed']++;
                    $report['failures'][] = [
                        'municipio' => (string) ($municipality['nome'] ?? ''),
                        'slug' => (string) ($municipality['slug'] ?? ''),
                        'categoria' => $category,
                        'erro' => $exception->getMessage(),
                    ];
                }
            }

            if ($importsForMunicipality > 0) {
                $report['municipalities_with_imports']++;
            }

            if (is_callable($progress)) {
                $progress($municipality, $importsForMunicipality, $report);
            }
        }

        $this->lastReport = $report;

        return (int) $report['imported'];
    }

    public function lastReport(): array
    {
        return $this->lastReport;
    }

    private function selectCandidate(array $municipality, string $category, int $minScore, int $searchLimit): ?array
    {
        $query = $this->buildDiscoveryQuery($municipality, $category);
        $candidates = $this->wikimedia->searchCandidates($query, $searchLimit);

        foreach ($candidates as &$candidate) {
            if (!$this->isImportableImageCandidate($candidate)) {
                $candidate['confidence_score'] = -1;
                continue;
            }

            $candidate['confidence_score'] = $this->confidenceScoreForCandidate($municipality, $category, $candidate);
        }
        unset($candidate);

        usort(
            $candidates,
            static fn (array $left, array $right): int => ((int) ($right['confidence_score'] ?? 0) <=> (int) ($left['confidence_score'] ?? 0))
        );

        foreach ($candidates as $candidate) {
            $score = (int) ($candidate['confidence_score'] ?? 0);
            if ($score < $minScore) {
                continue;
            }

            return $candidate;
        }

        return null;
    }

    private function isImportableImageCandidate(array $candidate): bool
    {
        $mediaType = strtoupper(trim((string) ($candidate['media_type'] ?? '')));
        $mime = strtolower(trim((string) ($candidate['mime'] ?? '')));
        $title = strtolower(trim((string) ($candidate['title'] ?? '')));

        if ($mediaType !== '' && $mediaType !== 'BITMAP' && $mediaType !== 'DRAWING') {
            return false;
        }

        if ($mime !== '' && !str_starts_with($mime, 'image/')) {
            return false;
        }

        foreach (['.pdf', '.djvu', '.tif', '.tiff'] as $blockedSuffix) {
            if (str_ends_with($title, $blockedSuffix)) {
                return false;
            }
        }

        return true;
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

        return max(0, min(100, $score));
    }

    private function normalizeSearchText(string $value): string
    {
        $normalized = str_replace('-', ' ', slugify($value));

        return trim(preg_replace('/\s+/', ' ', $normalized) ?? '');
    }
}
