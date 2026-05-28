<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/Config/helpers.php';
require dirname(__DIR__) . '/bootstrap/app.php';

\App\Core\Env::load(app_path('.env'));

$stateIdentifier = $argv[1] ?? 'PI';
$limit = isset($argv[2]) ? max(0, (int) $argv[2]) : 0;
$minScore = 0;
$categories = ['paisagem', 'bandeira', 'brasao', 'patrimonio', 'atrativo'];

foreach ($argv as $argument) {
    if (str_starts_with($argument, '--min-score=')) {
        $minScore = max(0, min(100, (int) substr($argument, 12)));
    }

    if (str_starts_with($argument, '--categories=')) {
        $categories = array_values(array_filter(
            array_map('trim', explode(',', (string) substr($argument, 13))),
            static fn (string $value): bool => $value !== ''
        ));
    }
}

$service = new \App\Services\WikimediaBulkImporterService();
$count = $service->importState((string) $stateIdentifier, null, [
    'limit' => $limit,
    'min_score' => $minScore,
    'categories' => $categories,
    'only_missing_coverage' => true,
    'status_curadoria' => 'publicado',
    'destaque' => false,
    'preencher_imagem_principal' => true,
    'search_limit' => 5,
    'progress' => static function (array $municipality, int $importsForMunicipality, array $report): void {
        fwrite(
            STDOUT,
            sprintf(
                "[PROCESSADO] %s | importadas-no-municipio=%d | processados=%d | importadas=%d | falhas=%d\n",
                (string) ($municipality['nome'] ?? 'Municipio'),
                $importsForMunicipality,
                (int) ($report['processed'] ?? 0),
                (int) ($report['imported'] ?? 0),
                (int) ($report['failed'] ?? 0)
            )
        );
    },
]);
$report = $service->lastReport();

fwrite(STDOUT, "ESTADO={$stateIdentifier}\n");
fwrite(STDOUT, 'CATEGORIAS=' . implode(',', $categories) . "\n");
fwrite(STDOUT, "MIN_SCORE={$minScore}\n");
fwrite(STDOUT, 'MUNICIPIOS_PROCESSADOS=' . (int) ($report['processed'] ?? 0) . "\n");
fwrite(STDOUT, 'MUNICIPIOS_COM_IMPORTACAO=' . (int) ($report['municipalities_with_imports'] ?? 0) . "\n");
fwrite(STDOUT, "MIDIAS_IMPORTADAS={$count}\n");
fwrite(STDOUT, 'DUPLICADAS=' . (int) ($report['skipped_duplicates'] ?? 0) . "\n");
fwrite(STDOUT, 'SEM_CANDIDATO=' . (int) ($report['skipped_without_candidate'] ?? 0) . "\n");
fwrite(STDOUT, 'CAPAS_PREENCHIDAS=' . (int) ($report['promoted'] ?? 0) . "\n");
fwrite(STDOUT, 'FALHAS=' . (int) ($report['failed'] ?? 0) . "\n");

if (!empty($report['failures'])) {
    foreach ($report['failures'] as $failure) {
        fwrite(
            STDOUT,
            sprintf(
                "FALHA_MUNICIPIO=%s | slug=%s | categoria=%s | erro=%s\n",
                (string) ($failure['municipio'] ?? ''),
                (string) ($failure['slug'] ?? ''),
                (string) ($failure['categoria'] ?? ''),
                (string) ($failure['erro'] ?? '')
            )
        );
    }
}
