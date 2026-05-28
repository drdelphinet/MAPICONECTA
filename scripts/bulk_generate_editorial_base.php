<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/Config/helpers.php';
require dirname(__DIR__) . '/bootstrap/app.php';

\App\Core\Env::load(app_path('.env'));

$stateIdentifier = $argv[1] ?? 'PI';

$states = new \App\Models\State();
$municipalities = new \App\Models\Municipality();
$scaffolder = new \App\Services\MunicipalityEditorialScaffolder();

$state = $states->findByCodeOrSigla((string) $stateIdentifier);
if ($state === null) {
    fwrite(STDERR, "Estado nao encontrado.\n");
    exit(1);
}

$items = $municipalities->adminList((int) $state['id_estado']);
$updated = 0;

foreach ($items as $municipality) {
    $payload = $scaffolder->buildPayload($municipality, null);
    $changed = false;

    foreach (['descricao_curta', 'historia', 'geografia', 'economia', 'cultura', 'turismo', 'educacao', 'curiosidades'] as $field) {
        if (($payload[$field] ?? '') !== (string) ($municipality[$field] ?? '')) {
            $changed = true;
            break;
        }
    }

    if (!$changed) {
        continue;
    }

    $municipalities->update((int) $municipality['id_municipio'], $payload);
    $updated++;
}

fwrite(STDOUT, "ESTADO=" . $state['sigla'] . "\n");
fwrite(STDOUT, "MUNICIPIOS_ATUALIZADOS={$updated}\n");
