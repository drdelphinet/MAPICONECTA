<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/Config/helpers.php';
require dirname(__DIR__) . '/bootstrap/app.php';

\App\Core\Env::load(app_path('.env'));

$sourcePath = app_path('public/assets/data/piaui-municipios.geojson');

if (!is_file($sourcePath)) {
    fwrite(STDERR, "Arquivo GeoJSON nao encontrado.\n");
    exit(1);
}

$raw = file_get_contents($sourcePath);
if ($raw === false) {
    fwrite(STDERR, "Falha ao ler o arquivo GeoJSON.\n");
    exit(1);
}

$geojson = json_decode($raw, true);
if (!is_array($geojson) || !isset($geojson['features']) || !is_array($geojson['features'])) {
    fwrite(STDERR, "GeoJSON invalido.\n");
    exit(1);
}

$pdo = \App\Core\Database::connection();
$rows = $pdo->query(
    "SELECT codigo_ibge, nome, slug, descricao_curta, latitude, longitude
     FROM municipios
     WHERE status_publicacao = 'publicado'"
)->fetchAll(PDO::FETCH_ASSOC);

$municipalitiesByCode = [];
foreach ($rows as $row) {
    $code = preg_replace('/\D+/', '', (string) ($row['codigo_ibge'] ?? ''));
    if ($code === '') {
        continue;
    }

    $municipalitiesByCode[$code] = $row;
}

$updated = 0;

foreach ($geojson['features'] as &$feature) {
    if (!isset($feature['properties']) || !is_array($feature['properties'])) {
        $feature['properties'] = [];
    }

    $code = preg_replace('/\D+/', '', (string) ($feature['properties']['id'] ?? $feature['properties']['codigo_ibge'] ?? ''));
    if ($code === '' || !isset($municipalitiesByCode[$code])) {
        continue;
    }

    $municipality = $municipalitiesByCode[$code];
    $feature['properties']['codigo_ibge'] = $code;
    $feature['properties']['slug'] = $municipality['slug'];
    $feature['properties']['municipio_slug'] = $municipality['slug'];
    $feature['properties']['name'] = $municipality['nome'];
    $feature['properties']['nome'] = $municipality['nome'];
    $feature['properties']['description'] = $municipality['descricao_curta'] ?: $municipality['nome'];
    if ($municipality['latitude'] !== null) {
        $feature['properties']['latitude'] = (float) $municipality['latitude'];
    }
    if ($municipality['longitude'] !== null) {
        $feature['properties']['longitude'] = (float) $municipality['longitude'];
    }
    $updated++;
}
unset($feature);

$encoded = json_encode($geojson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($encoded === false) {
    fwrite(STDERR, "Falha ao codificar o GeoJSON enriquecido.\n");
    exit(1);
}

$written = file_put_contents($sourcePath, $encoded);
if ($written === false) {
    fwrite(STDERR, "Falha ao gravar o arquivo GeoJSON enriquecido.\n");
    exit(1);
}

fwrite(STDOUT, "FEATURES_ENRIQUECIDAS={$updated}\n");
