<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/Config/helpers.php';
require dirname(__DIR__) . '/bootstrap/app.php';

\App\Core\Env::load(app_path('.env'));

$stateIdentifier = $argv[1] ?? 'PI';
$limit = isset($argv[2]) ? max(0, (int) $argv[2]) : 0;
$refreshAll = in_array('--refresh', $argv, true);

$service = new \App\Services\IbgeCityFactsService();
$count = $service->importStateFacts((string) $stateIdentifier, null, [
    'limit' => $limit,
    'only_missing' => !$refreshAll,
    'continue_on_error' => true,
    'delay_ms' => 120,
    'progress' => static function (array $municipality, string $status, array $report): void {
        fwrite(
            STDOUT,
            sprintf(
                "[%s] %s | processados=%d atualizados=%d falhas=%d\n",
                strtoupper($status),
                (string) ($municipality['nome'] ?? 'Municipio'),
                (int) ($report['processed'] ?? 0),
                (int) ($report['updated'] ?? 0),
                (int) ($report['failed'] ?? 0)
            )
        );
    },
]);
$report = $service->lastReport();

fwrite(STDOUT, "ESTADO={$stateIdentifier}\n");
fwrite(STDOUT, 'MODO=' . ($refreshAll ? 'refresh' : 'missing-only') . "\n");
fwrite(STDOUT, "MUNICIPIOS_ENRIQUECIDOS={$count}\n");
fwrite(STDOUT, 'PROCESSADOS=' . (int) ($report['processed'] ?? 0) . "\n");
fwrite(STDOUT, 'JA_COMPLETOS=' . (int) ($report['skipped_complete'] ?? 0) . "\n");
fwrite(STDOUT, 'SEM_DADOS=' . (int) ($report['skipped_without_data'] ?? 0) . "\n");
fwrite(STDOUT, 'FALHAS=' . (int) ($report['failed'] ?? 0) . "\n");

if (!empty($report['failures'])) {
    foreach ($report['failures'] as $failure) {
        fwrite(
            STDOUT,
            sprintf(
                "FALHA_MUNICIPIO=%s | slug=%s | erro=%s\n",
                (string) ($failure['municipio'] ?? ''),
                (string) ($failure['slug'] ?? ''),
                (string) ($failure['erro'] ?? '')
            )
        );
    }
}
