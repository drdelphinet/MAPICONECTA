<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\IntegrationLog;
use App\Models\Municipality;
use App\Models\State;
use RuntimeException;

final class IbgeCityFactsService
{
    private const BASE_URL = 'https://www.ibge.gov.br/cidades-e-estados';

    private array $lastReport = [];

    public function __construct(
        private readonly State $states = new State(),
        private readonly Municipality $municipalities = new Municipality(),
        private readonly IntegrationLog $logs = new IntegrationLog()
    ) {
    }

    public function importStateFacts(string $stateCodeOrSigla, ?int $userId = null, array $options = []): int
    {
        $state = $this->states->findByCodeOrSigla($stateCodeOrSigla);
        if ($state === null) {
            throw new RuntimeException('Estado nao encontrado para importacao factual.');
        }

        $endpoint = self::BASE_URL . '/' . strtolower((string) $state['sigla']) . '/';
        $items = $this->municipalities->adminList((int) $state['id_estado']);
        $onlyMissing = ($options['only_missing'] ?? true) !== false;
        $continueOnError = ($options['continue_on_error'] ?? true) !== false;
        $limit = max(0, (int) ($options['limit'] ?? 0));
        $delayMs = max(0, (int) ($options['delay_ms'] ?? 0));
        $progress = $options['progress'] ?? null;
        $updated = 0;
        $report = [
            'state' => strtoupper((string) $state['sigla']),
            'processed' => 0,
            'updated' => 0,
            'skipped_complete' => 0,
            'skipped_without_data' => 0,
            'failed' => 0,
            'failures' => [],
        ];

        try {
            foreach ($items as $municipality) {
                if ($onlyMissing && !$this->municipalityNeedsFacts($municipality)) {
                    $report['skipped_complete']++;
                    continue;
                }

                if ($limit > 0 && $report['processed'] >= $limit) {
                    break;
                }

                $report['processed']++;

                try {
                    $facts = $this->fetchMunicipalityFacts((string) $state['sigla'], (string) $municipality['slug']);
                } catch (RuntimeException $exception) {
                    $report['failed']++;
                    $report['failures'][] = [
                        'municipio' => (string) $municipality['nome'],
                        'slug' => (string) $municipality['slug'],
                        'erro' => $exception->getMessage(),
                    ];

                    if (is_callable($progress)) {
                        $progress($municipality, 'erro', $report);
                    }

                    if (!$continueOnError) {
                        throw $exception;
                    }

                    $this->applyDelay($delayMs);
                    continue;
                }

                if ($facts === null) {
                    $report['skipped_without_data']++;
                    if (is_callable($progress)) {
                        $progress($municipality, 'sem_dados', $report);
                    }

                    $this->applyDelay($delayMs);
                    continue;
                }

                $payload = $this->buildMergedPayload($municipality, $facts, $userId);
                if (!$this->payloadIntroducesFactChanges($municipality, $payload)) {
                    $report['skipped_complete']++;
                    if (is_callable($progress)) {
                        $progress($municipality, 'sem_alteracao', $report);
                    }

                    $this->applyDelay($delayMs);
                    continue;
                }

                $this->municipalities->update((int) $municipality['id_municipio'], $payload);
                $updated++;
                $report['updated']++;

                if (is_callable($progress)) {
                    $progress($municipality, 'atualizado', $report);
                }

                $this->applyDelay($delayMs);
            }

            $this->lastReport = $report;
            $this->logs->create([
                'fonte' => 'IBGE Cidades e Estados',
                'endpoint' => $endpoint,
                'parametros' => [
                    'uf' => strtoupper((string) $state['sigla']),
                    'only_missing' => $onlyMissing,
                    'limit' => $limit > 0 ? $limit : null,
                ],
                'status' => 'sucesso',
                'mensagem' => $report['failed'] > 0
                    ? 'Importacao factual concluida com falhas parciais.'
                    : 'Dados factuais municipais importados com sucesso.',
                'resposta_resumida' => $this->summarizeReport($report),
                'usuario_id' => $userId,
            ]);

            return $updated;
        } catch (RuntimeException $exception) {
            $this->lastReport = $report;
            $this->logs->create([
                'fonte' => 'IBGE Cidades e Estados',
                'endpoint' => $endpoint,
                'parametros' => ['uf' => strtoupper((string) $state['sigla'])],
                'status' => 'erro',
                'mensagem' => $exception->getMessage(),
                'resposta_resumida' => $this->summarizeReport($report),
                'usuario_id' => $userId,
            ]);

            throw $exception;
        }
    }

    public function lastReport(): array
    {
        return $this->lastReport;
    }

    public function fetchMunicipalityFacts(string $stateSigla, string $municipalitySlug): ?array
    {
        $url = self::BASE_URL . '/' . strtolower($stateSigla) . '/' . $municipalitySlug . '.html';
        $html = $this->fetchHtml($url);
        $decodedHtml = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $gentilicoValue = $this->extractIndicatorValue($decodedHtml, 'Gentílico');
        $areaValue = $this->extractIndicatorValue($decodedHtml, 'Área Territorial');
        $populationValue = $this->extractIndicatorValue($decodedHtml, 'População no último censo');
        $densityValue = $this->extractIndicatorValue($decodedHtml, 'Densidade demográfica');
        $gentilicoYear = $this->extractIndicatorYear($decodedHtml, 'Gentílico');
        $areaYear = $this->extractIndicatorYear($decodedHtml, 'Área Territorial');
        $populationYear = $this->extractIndicatorYear($decodedHtml, 'População no último censo');
        $densityYear = $this->extractIndicatorYear($decodedHtml, 'Densidade demográfica');

        $gentilico = $gentilicoValue !== null ? trim($gentilicoValue) : null;
        $area = $areaValue !== null ? $this->match('/([\d\.\,]+)/u', $areaValue) : null;
        $population = $populationValue !== null ? $this->match('/([\d\.\,]+)/u', $populationValue) : null;
        $density = $densityValue !== null ? $this->match('/([\d\.\,]+)/u', $densityValue) : null;

        if ($gentilico === null && $area === null && $population === null && $density === null) {
            return null;
        }

        return [
            'gentilico' => $gentilico !== null ? trim($gentilico) : null,
            'area' => $area !== null ? $this->parseDecimal($area) : null,
            'populacao' => $population !== null ? $this->parseInteger($population) : null,
            'densidade_demografica' => $density !== null ? $this->parseDecimal($density) : null,
            'fonte_dados' => $this->buildFactualSourceString($url, [
                'gentilico' => $gentilicoYear,
                'area' => $areaYear,
                'populacao' => $populationYear,
                'densidade_demografica' => $densityYear,
            ]),
        ];
    }

    private function buildMergedPayload(array $municipality, array $facts, ?int $userId): array
    {
        return [
            'id_estado' => (int) $municipality['id_estado'],
            'codigo_ibge' => (int) $municipality['codigo_ibge'],
            'nome' => $municipality['nome'],
            'slug' => $municipality['slug'],
            'descricao_curta' => (string) ($municipality['descricao_curta'] ?? ''),
            'historia' => (string) ($municipality['historia'] ?? ''),
            'geografia' => (string) ($municipality['geografia'] ?? ''),
            'economia' => (string) ($municipality['economia'] ?? ''),
            'cultura' => (string) ($municipality['cultura'] ?? ''),
            'turismo' => (string) ($municipality['turismo'] ?? ''),
            'educacao' => (string) ($municipality['educacao'] ?? ''),
            'curiosidades' => (string) ($municipality['curiosidades'] ?? ''),
            'populacao' => $this->stringOrIncoming($municipality['populacao'] ?? null, $facts['populacao'] ?? null),
            'area' => $this->stringOrIncoming($municipality['area'] ?? null, $facts['area'] ?? null),
            'densidade_demografica' => $this->stringOrIncoming($municipality['densidade_demografica'] ?? null, $facts['densidade_demografica'] ?? null),
            'gentilico' => $this->stringOrIncoming($municipality['gentilico'] ?? null, $facts['gentilico'] ?? null),
            'data_fundacao' => (string) ($municipality['data_fundacao'] ?? ''),
            'latitude' => (string) ($municipality['latitude'] ?? ''),
            'longitude' => (string) ($municipality['longitude'] ?? ''),
            'distancia_capital' => (string) ($municipality['distancia_capital'] ?? ''),
            'imagem_principal' => (string) ($municipality['imagem_principal'] ?? ''),
            'fonte_dados' => $this->mergeSource((string) ($municipality['fonte_dados'] ?? ''), (string) ($facts['fonte_dados'] ?? '')),
            'status_curadoria' => $municipality['status_curadoria'] ?? 'rascunho',
            'status_publicacao' => $municipality['status_publicacao'] ?? 'rascunho',
            'criado_por' => $municipality['criado_por'] ?? null,
            'atualizado_por' => $userId,
            'revisado_por' => $municipality['revisado_por'] ?? null,
            'publicado_por' => $municipality['publicado_por'] ?? null,
            'publicado_em' => $municipality['publicado_em'] ?? null,
        ];
    }

    private function stringOrIncoming(mixed $current, mixed $incoming): string
    {
        $currentString = trim((string) $current);
        if ($currentString !== '') {
            return $currentString;
        }

        return trim((string) $incoming);
    }

    private function municipalityNeedsFacts(array $municipality): bool
    {
        return trim((string) ($municipality['populacao'] ?? '')) === ''
            || trim((string) ($municipality['area'] ?? '')) === ''
            || trim((string) ($municipality['densidade_demografica'] ?? '')) === ''
            || trim((string) ($municipality['gentilico'] ?? '')) === '';
    }

    private function payloadIntroducesFactChanges(array $municipality, array $payload): bool
    {
        foreach (['populacao', 'area', 'densidade_demografica', 'gentilico', 'fonte_dados'] as $field) {
            if ((string) ($municipality[$field] ?? '') !== (string) ($payload[$field] ?? '')) {
                return true;
            }
        }

        return false;
    }

    private function mergeSource(string $current, string $incoming): string
    {
        $current = trim($current);
        $incoming = trim($incoming);

        if ($incoming === '') {
            return $current;
        }

        if ($current === '') {
            return $incoming;
        }

        if (str_contains($current, $incoming)) {
            return $current;
        }

        return $current . '; ' . $incoming;
    }

    private function summarizeReport(array $report): string
    {
        return sprintf(
            'Processados: %d | Atualizados: %d | Ja completos: %d | Sem dados: %d | Falhas: %d',
            (int) ($report['processed'] ?? 0),
            (int) ($report['updated'] ?? 0),
            (int) ($report['skipped_complete'] ?? 0),
            (int) ($report['skipped_without_data'] ?? 0),
            (int) ($report['failed'] ?? 0)
        );
    }

    private function applyDelay(int $delayMs): void
    {
        if ($delayMs > 0) {
            usleep($delayMs * 1000);
        }
    }

    private function fetchHtml(string $url): string
    {
        if (function_exists('curl_init')) {
            $html = $this->fetchHtmlWithCurl($url, true);
            if ($html !== null) {
                return $html;
            }

            $html = $this->fetchHtmlWithCurl($url, false);
            if ($html !== null) {
                return $html;
            }
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 20,
                'header' => "Accept: text/html\r\nUser-Agent: MapiConecta/1.0\r\n",
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        $html = @file_get_contents($url, false, $context);
        if ($html === false) {
            throw new RuntimeException('Falha ao consultar o portal IBGE Cidades e Estados.');
        }

        return $html;
    }

    private function extractIndicatorValue(string $html, string $label): ?string
    {
        $pattern = sprintf(
            '/<p>\s*%s\s*<\/p>\s*<\/div>\s*<p class=[\'"]ind-value[\'"]>(.*?)<\/p>/su',
            preg_quote($label, '/')
        );

        if (!preg_match($pattern, $html, $matches)) {
            return null;
        }

        $value = strip_tags((string) ($matches[1] ?? ''));
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/\[[0-9]{4}\]/u', '', $value) ?? $value;

        return preg_replace('/\s+/u', ' ', trim($value)) ?: null;
    }

    private function extractIndicatorYear(string $html, string $label): ?string
    {
        $pattern = sprintf(
            '/<p>\s*%s\s*<\/p>\s*<\/div>\s*<p class=[\'"]ind-value[\'"]>.*?<small>\s*&nbsp;*&nbsp;*&nbsp;*\[([0-9]{4})\]<\/small>/su',
            preg_quote($label, '/')
        );

        if (!preg_match($pattern, $html, $matches)) {
            return null;
        }

        return trim((string) ($matches[1] ?? '')) ?: null;
    }

    private function buildFactualSourceString(string $url, array $years): string
    {
        $parts = [
            'IBGE Cidades e Estados',
            'url=' . $url,
            'coleta=' . date('Y-m-d'),
        ];

        foreach ($years as $field => $year) {
            if ($year === null || $year === '') {
                continue;
            }

            $parts[] = $field . '=' . $year;
        }

        return implode('|', $parts);
    }

    private function fetchHtmlWithCurl(string $url, bool $verifySsl): ?string
    {
        $ch = curl_init($url);
        if ($ch === false) {
            return null;
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 8,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_ENCODING => '',
            CURLOPT_USERAGENT => 'MapiConecta/1.0',
            CURLOPT_HTTPHEADER => ['Accept: text/html'],
            CURLOPT_SSL_VERIFYPEER => $verifySsl,
            CURLOPT_SSL_VERIFYHOST => $verifySsl ? 2 : 0,
        ]);

        $html = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($html === false || $httpCode >= 400 || $error !== '') {
            return null;
        }

        return (string) $html;
    }

    private function match(string $pattern, string $text): ?string
    {
        if (!preg_match($pattern, $text, $matches)) {
            return null;
        }

        return trim((string) ($matches[1] ?? ''));
    }

    private function parseInteger(string $value): int
    {
        $normalized = preg_replace('/\D+/', '', $value) ?? '0';

        return (int) $normalized;
    }

    private function parseDecimal(string $value): float
    {
        $normalized = str_replace('.', '', $value);
        $normalized = str_replace(',', '.', $normalized);

        return (float) $normalized;
    }
}
