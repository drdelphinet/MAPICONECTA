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

$featureCentroids = [];
foreach ($geojson['features'] as $feature) {
    $properties = is_array($feature['properties'] ?? null) ? $feature['properties'] : [];
    $code = preg_replace('/\D+/', '', (string) ($properties['codigo_ibge'] ?? $properties['id'] ?? $properties['code'] ?? ''));
    if ($code === '') {
        continue;
    }

    $centroid = centroidFromGeometry($feature['geometry'] ?? null);
    if ($centroid === null) {
        continue;
    }

    $featureCentroids[$code] = $centroid;
}

$pdo = \App\Core\Database::connection();
$rows = $pdo->query(
    "SELECT id_municipio, codigo_ibge, nome
     FROM municipios
     WHERE status_publicacao = 'publicado'
       AND (latitude IS NULL OR longitude IS NULL)"
)->fetchAll(PDO::FETCH_ASSOC);

$updated = 0;
$missing = [];
$statement = $pdo->prepare(
    'UPDATE municipios
        SET latitude = :latitude,
            longitude = :longitude,
            atualizado_em = NOW()
      WHERE id_municipio = :id'
);

foreach ($rows as $row) {
    $code = preg_replace('/\D+/', '', (string) ($row['codigo_ibge'] ?? ''));
    if ($code === '' || !isset($featureCentroids[$code])) {
        $missing[] = $row['nome'] . ' (' . $row['codigo_ibge'] . ')';
        continue;
    }

    $centroid = $featureCentroids[$code];
    $statement->execute([
        'latitude' => number_format($centroid['lat'], 7, '.', ''),
        'longitude' => number_format($centroid['lng'], 7, '.', ''),
        'id' => (int) $row['id_municipio'],
    ]);
    $updated++;
}

fwrite(STDOUT, "COORDENADAS_ATUALIZADAS={$updated}\n");
fwrite(STDOUT, "SEM_MALHA=" . count($missing) . "\n");

if ($missing !== []) {
    fwrite(STDOUT, "MUNICIPIOS_SEM_MALHA=" . implode('; ', $missing) . "\n");
}

function centroidFromGeometry(mixed $geometry): ?array
{
    if (!is_array($geometry)) {
        return null;
    }

    $type = (string) ($geometry['type'] ?? '');
    $coordinates = $geometry['coordinates'] ?? null;
    if (!is_array($coordinates)) {
        return null;
    }

    if ($type === 'Polygon') {
        return centroidFromPolygon($coordinates);
    }

    if ($type === 'MultiPolygon') {
        return centroidFromMultiPolygon($coordinates);
    }

    return null;
}

function centroidFromMultiPolygon(array $multiPolygon): ?array
{
    $best = null;
    $bestArea = -1.0;

    foreach ($multiPolygon as $polygon) {
        if (!is_array($polygon)) {
            continue;
        }

        $candidate = centroidFromPolygon($polygon);
        if ($candidate === null) {
            continue;
        }

        if ($candidate['area'] > $bestArea) {
            $best = $candidate;
            $bestArea = $candidate['area'];
        }
    }

    return $best === null ? null : ['lat' => $best['lat'], 'lng' => $best['lng']];
}

function centroidFromPolygon(array $polygon): ?array
{
    if ($polygon === [] || !is_array($polygon[0] ?? null)) {
        return null;
    }

    $outerRing = $polygon[0];
    $centroid = centroidFromRing($outerRing);

    if ($centroid !== null) {
        return $centroid;
    }

    return averagePointFromRing($outerRing);
}

function centroidFromRing(array $ring): ?array
{
    if (count($ring) < 3) {
        return null;
    }

    $twiceArea = 0.0;
    $centroidX = 0.0;
    $centroidY = 0.0;
    $pointCount = count($ring);

    for ($i = 0; $i < $pointCount - 1; $i++) {
        $current = $ring[$i] ?? null;
        $next = $ring[$i + 1] ?? null;

        if (!validPoint($current) || !validPoint($next)) {
            continue;
        }

        $x1 = (float) $current[0];
        $y1 = (float) $current[1];
        $x2 = (float) $next[0];
        $y2 = (float) $next[1];

        $cross = ($x1 * $y2) - ($x2 * $y1);
        $twiceArea += $cross;
        $centroidX += ($x1 + $x2) * $cross;
        $centroidY += ($y1 + $y2) * $cross;
    }

    if (abs($twiceArea) < 1e-12) {
        return null;
    }

    $area = abs($twiceArea) / 2;

    return [
        'lat' => $centroidY / (3 * $twiceArea),
        'lng' => $centroidX / (3 * $twiceArea),
        'area' => $area,
    ];
}

function averagePointFromRing(array $ring): ?array
{
    $sumX = 0.0;
    $sumY = 0.0;
    $count = 0;

    foreach ($ring as $point) {
        if (!validPoint($point)) {
            continue;
        }

        $sumX += (float) $point[0];
        $sumY += (float) $point[1];
        $count++;
    }

    if ($count === 0) {
        return null;
    }

    return [
        'lat' => $sumY / $count,
        'lng' => $sumX / $count,
        'area' => 0.0,
    ];
}

function validPoint(mixed $point): bool
{
    return is_array($point)
        && isset($point[0], $point[1])
        && is_numeric($point[0])
        && is_numeric($point[1]);
}
