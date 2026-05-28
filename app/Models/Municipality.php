<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Municipality
{
    private const OFFICIAL_TOTALS_BY_STATE = [
        'piaui' => 224,
    ];

    public function officialCount(?string $stateSlug = null): int
    {
        if ($stateSlug !== null && $stateSlug !== '' && isset(self::OFFICIAL_TOTALS_BY_STATE[$stateSlug])) {
            return self::OFFICIAL_TOTALS_BY_STATE[$stateSlug];
        }

        if ($stateSlug === null || $stateSlug === '') {
            return array_sum(self::OFFICIAL_TOTALS_BY_STATE);
        }

        return $this->publicCount($stateSlug);
    }

    public function publicCount(?string $stateSlug = null): int
    {
        $sql = "SELECT COUNT(*)
                FROM municipios m
                INNER JOIN estados e ON e.id_estado = m.id_estado
                WHERE m.status_publicacao = 'publicado'";
        $params = [];

        if ($stateSlug !== null && $stateSlug !== '') {
            $sql .= ' AND e.slug = :state_slug';
            $params['state_slug'] = $stateSlug;
        }

        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn();
    }

    public function featuredPublic(int $limit = 3): array
    {
        $limit = max(1, $limit);
        $statement = Database::connection()->prepare(
            "SELECT m.*, e.nome AS estado_nome, e.sigla AS estado_sigla
             FROM municipios m
             INNER JOIN estados e ON e.id_estado = m.id_estado
             WHERE m.status_publicacao = 'publicado'
             ORDER BY m.populacao DESC, m.nome ASC
             LIMIT {$limit}"
        );
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function featuredRichPublic(int $limit = 3): array
    {
        $featured = $this->featuredPublic(60);
        $ranked = $this->enrichWithEditorialReadiness($featured);
        $rich = array_values(array_filter(
            $ranked,
            static fn (array $municipality): bool => ($municipality['editorial_status'] ?? '') === 'autoral'
        ));

        return array_slice($rich !== [] ? $rich : $ranked, 0, $limit);
    }

    public function recommendedPublicForUser(int $userId, int $limit = 4): array
    {
        $limit = max(1, $limit);
        $statement = Database::connection()->prepare(
            "SELECT m.*, e.nome AS estado_nome, e.sigla AS estado_sigla,
                    CASE WHEN f.id_municipio IS NULL THEN 0 ELSE 1 END AS favorito
             FROM municipios m
             INNER JOIN estados e ON e.id_estado = m.id_estado
             LEFT JOIN municipios_favoritos f
                 ON f.id_municipio = m.id_municipio
                AND f.id_usuario = :usuario
             LEFT JOIN municipios_visitados v
                 ON v.id_municipio = m.id_municipio
                AND v.id_usuario = :usuario
             WHERE m.status_publicacao = 'publicado'
               AND v.id_municipio IS NULL
             ORDER BY favorito DESC, m.populacao DESC, m.nome ASC
             LIMIT {$limit}"
        );
        $statement->bindValue('usuario', $userId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function favoritesCountForUser(int $userId): int
    {
        $statement = Database::connection()->prepare(
            'SELECT COUNT(*) FROM municipios_favoritos WHERE id_usuario = :usuario'
        );
        $statement->execute(['usuario' => $userId]);

        return (int) $statement->fetchColumn();
    }

    public function favoriteListForUser(int $userId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT m.*, e.sigla AS estado_sigla
             FROM municipios_favoritos f
             INNER JOIN municipios m ON m.id_municipio = f.id_municipio
             INNER JOIN estados e ON e.id_estado = m.id_estado
             WHERE f.id_usuario = :usuario
             ORDER BY f.criado_em DESC, m.nome ASC"
        );
        $statement->execute(['usuario' => $userId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function visitedCountForUser(int $userId): int
    {
        $statement = Database::connection()->prepare(
            'SELECT COUNT(*) FROM municipios_visitados WHERE id_usuario = :usuario'
        );
        $statement->execute(['usuario' => $userId]);

        return (int) $statement->fetchColumn();
    }

    public function visitedListForUser(int $userId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT m.*, e.sigla AS estado_sigla, v.visitado_em, v.origem
             FROM municipios_visitados v
             INNER JOIN municipios m ON m.id_municipio = v.id_municipio
             INNER JOIN estados e ON e.id_estado = m.id_estado
             WHERE v.id_usuario = :usuario
             ORDER BY v.visitado_em DESC, m.nome ASC"
        );
        $statement->execute(['usuario' => $userId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function isFavoritedByUser(int $municipalityId, int $userId): bool
    {
        $statement = Database::connection()->prepare(
            'SELECT 1 FROM municipios_favoritos WHERE id_usuario = :usuario AND id_municipio = :municipio LIMIT 1'
        );
        $statement->execute([
            'usuario' => $userId,
            'municipio' => $municipalityId,
        ]);

        return $statement->fetchColumn() !== false;
    }

    public function isVisitedByUser(int $municipalityId, int $userId): bool
    {
        $statement = Database::connection()->prepare(
            'SELECT 1 FROM municipios_visitados WHERE id_usuario = :usuario AND id_municipio = :municipio LIMIT 1'
        );
        $statement->execute([
            'usuario' => $userId,
            'municipio' => $municipalityId,
        ]);

        return $statement->fetchColumn() !== false;
    }

    public function toggleFavorite(int $municipalityId, int $userId): bool
    {
        if ($this->isFavoritedByUser($municipalityId, $userId)) {
            $statement = Database::connection()->prepare(
                'DELETE FROM municipios_favoritos WHERE id_usuario = :usuario AND id_municipio = :municipio'
            );
            $statement->execute([
                'usuario' => $userId,
                'municipio' => $municipalityId,
            ]);

            return false;
        }

        $statement = Database::connection()->prepare(
            'INSERT INTO municipios_favoritos (id_usuario, id_municipio) VALUES (:usuario, :municipio)'
        );
        $statement->execute([
            'usuario' => $userId,
            'municipio' => $municipalityId,
        ]);

        return true;
    }

    public function toggleVisited(int $municipalityId, int $userId, string $origin = 'manual'): bool
    {
        if ($this->isVisitedByUser($municipalityId, $userId)) {
            $statement = Database::connection()->prepare(
                'DELETE FROM municipios_visitados WHERE id_usuario = :usuario AND id_municipio = :municipio'
            );
            $statement->execute([
                'usuario' => $userId,
                'municipio' => $municipalityId,
            ]);

            return false;
        }

        $statement = Database::connection()->prepare(
            'INSERT INTO municipios_visitados (id_usuario, id_municipio, origem) VALUES (:usuario, :municipio, :origem)'
        );
        $statement->execute([
            'usuario' => $userId,
            'municipio' => $municipalityId,
            'origem' => $origin,
        ]);

        return true;
    }

    public function markVisitedIfMissing(int $municipalityId, int $userId, string $origin = 'manual'): void
    {
        if ($this->isVisitedByUser($municipalityId, $userId)) {
            return;
        }

        $statement = Database::connection()->prepare(
            'INSERT INTO municipios_visitados (id_usuario, id_municipio, origem) VALUES (:usuario, :municipio, :origem)'
        );
        $statement->execute([
            'usuario' => $userId,
            'municipio' => $municipalityId,
            'origem' => $origin,
        ]);
    }

    public function explorationPercentForUser(int $userId): int
    {
        $visited = $this->visitedCountForUser($userId);
        $total = $this->publicCount();

        if ($total <= 0) {
            return 0;
        }

        return (int) floor(($visited / $total) * 100);
    }

    public function adminList(?int $stateId = null, string $search = '', string $coordinatesFilter = '', string $publicationFilter = ''): array
    {
        $sql = 'SELECT m.*, e.nome AS estado_nome, e.sigla AS estado_sigla, u1.nome AS criado_por_nome, u2.nome AS revisado_por_nome, u3.nome AS publicado_por_nome
                FROM municipios m
                INNER JOIN estados e ON e.id_estado = m.id_estado
                LEFT JOIN usuarios u1 ON u1.id_usuario = m.criado_por
                LEFT JOIN usuarios u2 ON u2.id_usuario = m.revisado_por
                LEFT JOIN usuarios u3 ON u3.id_usuario = m.publicado_por
                WHERE 1 = 1';
        $params = [];

        if ($stateId !== null) {
            $sql .= ' AND m.id_estado = :state_id';
            $params['state_id'] = $stateId;
        }

        if ($search !== '') {
            $sql .= ' AND (m.nome LIKE :search OR m.codigo_ibge LIKE :search OR m.slug LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        if ($coordinatesFilter === 'with') {
            $sql .= ' AND m.latitude IS NOT NULL AND m.longitude IS NOT NULL';
        } elseif ($coordinatesFilter === 'without') {
            $sql .= ' AND (m.latitude IS NULL OR m.longitude IS NULL)';
        }

        if ($publicationFilter !== '') {
            $sql .= ' AND m.status_publicacao = :publication_filter';
            $params['publication_filter'] = $publicationFilter;
        }

        $sql .= ' ORDER BY e.nome ASC, m.nome ASC';

        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function publishedWithoutVisualCoverage(?int $stateId = null): array
    {
        $hasMediaTable = Database::tableExists('midias_municipio');
        $sql = "SELECT
                    m.id_municipio,
                    m.nome,
                    m.slug,
                    m.codigo_ibge,
                    m.imagem_principal,
                    e.nome AS estado_nome,
                    e.sigla AS estado_sigla,";
        if ($hasMediaTable) {
            $sql .= "
                    COALESCE(media_stats.image_count, 0) AS image_count,
                    COALESCE(media_stats.highlighted_count, 0) AS highlighted_count
                FROM municipios m
                INNER JOIN estados e ON e.id_estado = m.id_estado
                LEFT JOIN (
                    SELECT
                        mm.id_municipio,
                        SUM(CASE WHEN mm.tipo = 'imagem' AND mm.status_curadoria = 'publicado' THEN 1 ELSE 0 END) AS image_count,
                        SUM(CASE WHEN mm.tipo = 'imagem' AND mm.status_curadoria = 'publicado' AND mm.destaque = 1 THEN 1 ELSE 0 END) AS highlighted_count
                    FROM midias_municipio mm
                    GROUP BY mm.id_municipio
                ) AS media_stats ON media_stats.id_municipio = m.id_municipio
                WHERE m.status_publicacao = 'publicado'";
        } else {
            $sql .= "
                    0 AS image_count,
                    0 AS highlighted_count
                FROM municipios m
                INNER JOIN estados e ON e.id_estado = m.id_estado
                WHERE m.status_publicacao = 'publicado'";
        }
        $params = [];

        if ($stateId !== null) {
            $sql .= ' AND m.id_estado = :state_id';
            $params['state_id'] = $stateId;
        }

        $sql .= " AND (
                    COALESCE(NULLIF(m.imagem_principal, ''), '') = ''";
        if ($hasMediaTable) {
            $sql .= "
                    OR COALESCE(media_stats.image_count, 0) = 0";
        }
        $sql .= "
                 )
                 ORDER BY e.nome ASC, m.nome ASC";

        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function publicList(string $search = '', ?string $stateSlug = null): array
    {
        return $this->publicListPaginated($search, $stateSlug, 1000, 0);
    }

    public function searchPublic(string $search, int $limit = 8): array
    {
        $search = trim($search);
        if ($search === '') {
            return [];
        }

        $limit = max(1, $limit);
        $statement = Database::connection()->prepare(
            "SELECT m.id_municipio, m.nome, m.slug, m.descricao_curta, e.sigla AS estado_sigla
             FROM municipios m
             INNER JOIN estados e ON e.id_estado = m.id_estado
             WHERE m.status_publicacao = 'publicado'
               AND (
                    m.nome LIKE :search_nome
                    OR m.descricao_curta LIKE :search_descricao
                    OR m.historia LIKE :search_historia
                    OR m.cultura LIKE :search_cultura
                    OR m.turismo LIKE :search_turismo
               )
             ORDER BY
                CASE WHEN m.nome LIKE :search_prefix THEN 0 ELSE 1 END,
                m.nome ASC
             LIMIT {$limit}"
        );
        $statement->execute([
            'search_nome' => '%' . $search . '%',
            'search_descricao' => '%' . $search . '%',
            'search_historia' => '%' . $search . '%',
            'search_cultura' => '%' . $search . '%',
            'search_turismo' => '%' . $search . '%',
            'search_prefix' => $search . '%',
        ]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function publicListCount(string $search = '', ?string $stateSlug = null): int
    {
        $sql = "SELECT COUNT(*)
                FROM municipios m
                INNER JOIN estados e ON e.id_estado = m.id_estado
                WHERE m.status_publicacao = 'publicado'";
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (m.nome LIKE :search OR m.descricao_curta LIKE :search OR e.nome LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        if ($stateSlug !== null && $stateSlug !== '') {
            $sql .= ' AND e.slug = :state_slug';
            $params['state_slug'] = $stateSlug;
        }

        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn();
    }

    public function publicListPaginated(string $search = '', ?string $stateSlug = null, int $limit = 24, int $offset = 0): array
    {
        $limit = max(1, $limit);
        $offset = max(0, $offset);
        $sql = "SELECT m.*, e.nome AS estado_nome, e.sigla AS estado_sigla, e.slug AS estado_slug
                FROM municipios m
                INNER JOIN estados e ON e.id_estado = m.id_estado
                WHERE m.status_publicacao = 'publicado'";
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (m.nome LIKE :search OR m.descricao_curta LIKE :search OR e.nome LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        if ($stateSlug !== null && $stateSlug !== '') {
            $sql .= ' AND e.slug = :state_slug';
            $params['state_slug'] = $stateSlug;
        }

        $sql .= " ORDER BY m.nome ASC LIMIT {$limit} OFFSET {$offset}";

        $statement = Database::connection()->prepare($sql);
        foreach ($params as $key => $value) {
            $statement->bindValue(':' . $key, $value);
        }
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function publicWithoutCoordinates(?string $stateSlug = null, int $limit = 12): array
    {
        $limit = max(1, $limit);
        $sql = "SELECT m.*, e.nome AS estado_nome, e.sigla AS estado_sigla, e.slug AS estado_slug
                FROM municipios m
                INNER JOIN estados e ON e.id_estado = m.id_estado
                WHERE m.status_publicacao = 'publicado'
                  AND (m.latitude IS NULL OR m.longitude IS NULL)";
        $params = [];

        if ($stateSlug !== null && $stateSlug !== '') {
            $sql .= ' AND e.slug = :state_slug';
            $params['state_slug'] = $stateSlug;
        }

        $sql .= " ORDER BY m.nome ASC LIMIT {$limit}";

        $statement = Database::connection()->prepare($sql);
        foreach ($params as $key => $value) {
            $statement->bindValue(':' . $key, $value);
        }
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function publicWithoutCoordinatesCount(?string $stateSlug = null): int
    {
        $sql = "SELECT COUNT(*)
                FROM municipios m
                INNER JOIN estados e ON e.id_estado = m.id_estado
                WHERE m.status_publicacao = 'publicado'
                  AND (m.latitude IS NULL OR m.longitude IS NULL)";
        $params = [];

        if ($stateSlug !== null && $stateSlug !== '') {
            $sql .= ' AND e.slug = :state_slug';
            $params['state_slug'] = $stateSlug;
        }

        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn();
    }

    public function publicWithCoordinatesCount(?string $stateSlug = null): int
    {
        $sql = "SELECT COUNT(*)
                FROM municipios m
                INNER JOIN estados e ON e.id_estado = m.id_estado
                WHERE m.status_publicacao = 'publicado'
                  AND m.latitude IS NOT NULL
                  AND m.longitude IS NOT NULL";
        $params = [];

        if ($stateSlug !== null && $stateSlug !== '') {
            $sql .= ' AND e.slug = :state_slug';
            $params['state_slug'] = $stateSlug;
        }

        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn();
    }

    public function publicMissingFromGeoJson(?string $stateSlug = null, int $limit = 12): array
    {
        $codes = $this->geoJsonMunicipalityCodes();
        if ($codes === []) {
            return [];
        }

        $sql = "SELECT m.*, e.nome AS estado_nome, e.sigla AS estado_sigla, e.slug AS estado_slug
                FROM municipios m
                INNER JOIN estados e ON e.id_estado = m.id_estado
                WHERE m.status_publicacao = 'publicado'";
        $params = [];

        if ($stateSlug !== null && $stateSlug !== '') {
            $sql .= ' AND e.slug = :state_slug';
            $params['state_slug'] = $stateSlug;
        }

        $sql .= ' ORDER BY m.nome ASC';

        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        $missing = [];
        foreach ($rows as $row) {
            $code = preg_replace('/\D+/', '', (string) ($row['codigo_ibge'] ?? ''));
            if ($code === '' || isset($codes[$code])) {
                continue;
            }

            $missing[] = $row;
            if (count($missing) >= $limit) {
                break;
            }
        }

        return $missing;
    }

    public function publicMissingFromGeoJsonCount(?string $stateSlug = null): int
    {
        return count($this->publicMissingFromGeoJson($stateSlug, 1000));
    }

    public function municipalityExistsInGeoJson(string $code): bool
    {
        $normalized = preg_replace('/\D+/', '', $code);
        if ($normalized === '') {
            return false;
        }

        $codes = $this->geoJsonMunicipalityCodes();

        return isset($codes[$normalized]);
    }

    public function publicForMap(?string $stateSlug = null, ?string $municipalitySlug = null): array
    {
        $sql = "SELECT m.id_municipio, m.codigo_ibge, m.nome, m.slug, m.descricao_curta, m.latitude, m.longitude, m.populacao,
                       e.nome AS estado_nome, e.sigla AS estado_sigla, e.slug AS estado_slug
                FROM municipios m
                INNER JOIN estados e ON e.id_estado = m.id_estado
                WHERE m.status_publicacao = 'publicado'
                  AND m.latitude IS NOT NULL
                  AND m.longitude IS NOT NULL";
        $params = [];

        if ($stateSlug !== null && $stateSlug !== '') {
            $sql .= ' AND e.slug = :state_slug';
            $params['state_slug'] = $stateSlug;
        }

        if ($municipalitySlug !== null && $municipalitySlug !== '') {
            $sql .= ' AND m.slug = :municipality_slug';
            $params['municipality_slug'] = $municipalitySlug;
        }

        $sql .= ' ORDER BY m.nome ASC';

        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function publicGeoEntities(?string $stateSlug = null): array
    {
        $sql = "SELECT m.id_municipio, m.codigo_ibge, m.nome, m.slug, m.descricao_curta, m.latitude, m.longitude,
                       e.sigla AS estado_sigla
                FROM municipios m
                INNER JOIN estados e ON e.id_estado = m.id_estado
                WHERE m.status_publicacao = 'publicado'";
        $params = [];

        if ($stateSlug !== null && $stateSlug !== '') {
            $sql .= ' AND e.slug = :state_slug';
            $params['state_slug'] = $stateSlug;
        }

        $sql .= ' ORDER BY m.nome ASC';

        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function publicOptions(?string $stateSlug = null): array
    {
        $sql = "SELECT m.nome, m.slug
                FROM municipios m
                INNER JOIN estados e ON e.id_estado = m.id_estado
                WHERE m.status_publicacao = 'publicado'";
        $params = [];

        if ($stateSlug !== null && $stateSlug !== '') {
            $sql .= ' AND e.slug = :state_slug';
            $params['state_slug'] = $stateSlug;
        }

        $sql .= ' ORDER BY m.nome ASC';

        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT m.*, e.nome AS estado_nome, e.sigla AS estado_sigla, e.slug AS estado_slug,
                    u1.nome AS criado_por_nome, u2.nome AS atualizado_por_nome, u3.nome AS revisado_por_nome, u4.nome AS publicado_por_nome
             FROM municipios m
             INNER JOIN estados e ON e.id_estado = m.id_estado
             LEFT JOIN usuarios u1 ON u1.id_usuario = m.criado_por
             LEFT JOIN usuarios u2 ON u2.id_usuario = m.atualizado_por
             LEFT JOIN usuarios u3 ON u3.id_usuario = m.revisado_por
             LEFT JOIN usuarios u4 ON u4.id_usuario = m.publicado_por
             WHERE m.id_municipio = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $municipality = $statement->fetch(PDO::FETCH_ASSOC);

        return $municipality ?: null;
    }

    public function findByIdentifier(string $identifier): ?array
    {
        $identifier = trim($identifier);
        if ($identifier === '') {
            return null;
        }

        $normalizedCode = preg_replace('/\D+/', '', $identifier) ?? '';
        $statement = Database::connection()->prepare(
            'SELECT m.*, e.nome AS estado_nome, e.sigla AS estado_sigla, e.slug AS estado_slug
             FROM municipios m
             INNER JOIN estados e ON e.id_estado = m.id_estado
             WHERE m.slug = :slug
                OR m.nome = :nome
                OR m.codigo_ibge = :codigo
             LIMIT 1'
        );
        $statement->execute([
            'slug' => $identifier,
            'nome' => $identifier,
            'codigo' => $normalizedCode !== '' ? (int) $normalizedCode : 0,
        ]);
        $municipality = $statement->fetch(PDO::FETCH_ASSOC);

        return $municipality ?: null;
    }

    public function findPublishedBySlug(string $slug): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT m.*, e.nome AS estado_nome, e.sigla AS estado_sigla
             FROM municipios m
             INNER JOIN estados e ON e.id_estado = m.id_estado
             WHERE m.slug = :slug AND m.status_publicacao = 'publicado'
             LIMIT 1"
        );
        $statement->execute(['slug' => $slug]);
        $municipality = $statement->fetch(PDO::FETCH_ASSOC);

        return $municipality ?: null;
    }

    public function enrichWithEditorialReadiness(array $municipalities): array
    {
        return array_map(fn (array $municipality): array => $this->editorialCoverage($municipality), $municipalities);
    }

    public function enrichWithFactualCoverage(array $municipalities): array
    {
        return array_map(fn (array $municipality): array => $this->factualCoverage($municipality), $municipalities);
    }

    public function editorialCoverage(array $municipality): array
    {
        $sections = $this->editorialSectionLabels();

        $readySections = 0;
        $authoredSections = 0;
        foreach (array_keys($sections) as $field) {
            $value = (string) ($municipality[$field] ?? '');
            if (!$this->isPlaceholderContent($value)) {
                $readySections++;
            }
            if ($this->isAuthoredContent($value)) {
                $authoredSections++;
            }
        }

        $score = (int) floor(($readySections / count($sections)) * 100);
        $authoredScore = (int) floor(($authoredSections / count($sections)) * 100);
        $status = 'base';
        if ($authoredScore >= 75) {
            $status = 'autoral';
        } elseif ($authoredScore >= 35) {
            $status = 'em_curadoria';
        }

        $municipality['editorial_ready_sections'] = $readySections;
        $municipality['editorial_total_sections'] = count($sections);
        $municipality['editorial_score'] = $score;
        $municipality['editorial_authored_sections'] = $authoredSections;
        $municipality['editorial_authored_score'] = $authoredScore;
        $municipality['editorial_status'] = $status;
        $municipality['editorial_missing_sections'] = [];

        foreach ($sections as $field => $label) {
            if ($this->isPlaceholderContent((string) ($municipality[$field] ?? ''))) {
                $municipality['editorial_missing_sections'][] = $label;
            }
        }

        return $municipality;
    }

    public function factualCoverage(array $municipality): array
    {
        $fields = $this->factualFieldLabels();
        $ready = 0;
        $missing = [];
        $evidence = $this->parseFactualEvidence((string) ($municipality['fonte_dados'] ?? ''));

        foreach ($fields as $field => $label) {
            if (trim((string) ($municipality[$field] ?? '')) !== '') {
                $ready++;
                continue;
            }

            $missing[] = $label;
        }

        $municipality['facts_ready_fields'] = $ready;
        $municipality['facts_total_fields'] = count($fields);
        $municipality['facts_score'] = (int) floor(($ready / count($fields)) * 100);
        $municipality['facts_missing_fields'] = $missing;
        $municipality['facts_status'] = $ready === count($fields) ? 'completo' : ($ready > 0 ? 'parcial' : 'vazio');
        $municipality['facts_evidence'] = $evidence;
        $municipality['facts_access_date'] = $evidence['coleta'] ?? null;
        $municipality['facts_source_url'] = $evidence['url'] ?? null;

        return $municipality;
    }

    public function editorialSummary(?string $stateSlug = null): array
    {
        $rows = $this->enrichWithEditorialReadiness($this->publicList('', $stateSlug));
        $summary = [
            'autoral' => 0,
            'em_curadoria' => 0,
            'base' => 0,
        ];

        foreach ($rows as $row) {
            $status = $row['editorial_status'] ?? 'base';
            $summary[$status] = ($summary[$status] ?? 0) + 1;
        }

        return $summary;
    }

    public function editorialPriorityList(?string $stateSlug = null, int $limit = 8): array
    {
        $rows = $this->enrichWithEditorialReadiness($this->publicList('', $stateSlug));
        usort($rows, static function (array $left, array $right): int {
            $scoreComparison = ((int) ($left['editorial_authored_score'] ?? 0)) <=> ((int) ($right['editorial_authored_score'] ?? 0));
            if ($scoreComparison !== 0) {
                return $scoreComparison;
            }

            return strcmp((string) ($left['nome'] ?? ''), (string) ($right['nome'] ?? ''));
        });

        return array_slice($rows, 0, $limit);
    }

    public function factualSummary(?string $stateSlug = null): array
    {
        $rows = $this->enrichWithFactualCoverage($this->publicList('', $stateSlug));
        $summary = [
            'completo' => 0,
            'parcial' => 0,
            'vazio' => 0,
        ];

        foreach ($rows as $row) {
            $status = $row['facts_status'] ?? 'vazio';
            $summary[$status] = ($summary[$status] ?? 0) + 1;
        }

        return $summary;
    }

    public function create(array $data): int
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO municipios (
                id_estado, codigo_ibge, nome, slug, descricao_curta, historia, geografia, economia, cultura, turismo,
                educacao, curiosidades, populacao, area, densidade_demografica, gentilico, data_fundacao, latitude,
                longitude, distancia_capital, imagem_principal, fonte_dados, status_curadoria, status_publicacao,
                criado_por, atualizado_por, revisado_por, publicado_por, publicado_em
            ) VALUES (
                :id_estado, :codigo_ibge, :nome, :slug, :descricao_curta, :historia, :geografia, :economia, :cultura, :turismo,
                :educacao, :curiosidades, :populacao, :area, :densidade_demografica, :gentilico, :data_fundacao, :latitude,
                :longitude, :distancia_capital, :imagem_principal, :fonte_dados, :status_curadoria, :status_publicacao,
                :criado_por, :atualizado_por, :revisado_por, :publicado_por, :publicado_em
            )'
        );

        $statement->execute($this->payload($data));

        return (int) Database::connection()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $payload = $this->payload($data);
        $payload['id'] = $id;
        unset($payload['criado_por']);

        $statement = Database::connection()->prepare(
            'UPDATE municipios SET
                id_estado = :id_estado,
                codigo_ibge = :codigo_ibge,
                nome = :nome,
                slug = :slug,
                descricao_curta = :descricao_curta,
                historia = :historia,
                geografia = :geografia,
                economia = :economia,
                cultura = :cultura,
                turismo = :turismo,
                educacao = :educacao,
                curiosidades = :curiosidades,
                populacao = :populacao,
                area = :area,
                densidade_demografica = :densidade_demografica,
                gentilico = :gentilico,
                data_fundacao = :data_fundacao,
                latitude = :latitude,
                longitude = :longitude,
                distancia_capital = :distancia_capital,
                imagem_principal = :imagem_principal,
                fonte_dados = :fonte_dados,
                status_curadoria = :status_curadoria,
                status_publicacao = :status_publicacao,
                atualizado_por = :atualizado_por,
                revisado_por = :revisado_por,
                publicado_por = :publicado_por,
                publicado_em = :publicado_em
             WHERE id_municipio = :id'
        );

        $statement->execute($payload);
    }

    public function promoteMainImageIfEmpty(int $id, string $imageUrl, ?int $userId = null): bool
    {
        $imageUrl = trim($imageUrl);
        if ($imageUrl === '') {
            return false;
        }

        $statement = Database::connection()->prepare(
            "UPDATE municipios
             SET imagem_principal = :imagem_principal,
                 atualizado_por = :atualizado_por
             WHERE id_municipio = :id
               AND (imagem_principal IS NULL OR imagem_principal = '')"
        );
        $statement->bindValue('imagem_principal', $imageUrl);
        $statement->bindValue('id', $id, PDO::PARAM_INT);
        $statement->bindValue('atualizado_por', $userId, $userId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $statement->execute();

        return $statement->rowCount() > 0;
    }

    public function queue(string $status = 'enviado_revisao'): array
    {
        $statement = Database::connection()->prepare(
            'SELECT m.*, e.nome AS estado_nome, e.sigla AS estado_sigla
             FROM municipios m
             INNER JOIN estados e ON e.id_estado = m.id_estado
             WHERE m.status_curadoria = :status
             ORDER BY m.atualizado_em DESC, m.nome ASC'
        );
        $statement->execute(['status' => $status]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function workflowSummary(): array
    {
        $statement = Database::connection()->query(
            'SELECT status_curadoria, COUNT(*) AS total
             FROM municipios
             GROUP BY status_curadoria'
        );

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        $summary = [];

        foreach ($rows as $row) {
            $summary[$row['status_curadoria']] = (int) $row['total'];
        }

        return $summary;
    }

    public function bulkPublishByState(int $stateId, ?int $userId = null): int
    {
        $statement = Database::connection()->prepare(
            "UPDATE municipios
             SET descricao_curta = COALESCE(NULLIF(descricao_curta, ''), CONCAT(nome, ' faz parte do acervo inicial do MAPI CONECTA e aguarda enriquecimento editorial.')),
                 geografia = COALESCE(NULLIF(geografia, ''), CONCAT('Conteudo geografico inicial para ', nome, ' ainda sera detalhado pela equipe editorial.')),
                 cultura = COALESCE(NULLIF(cultura, ''), CONCAT('Conteudo cultural inicial para ', nome, ' ainda sera detalhado pela equipe editorial.')),
                 turismo = COALESCE(NULLIF(turismo, ''), CONCAT('Conteudo turistico inicial para ', nome, ' ainda sera detalhado pela equipe editorial.')),
                 status_curadoria = 'publicado',
                 status_publicacao = 'publicado',
                 atualizado_por = :atualizado_por,
                 revisado_por = :revisado_por,
                 publicado_por = :publicado_por,
                 publicado_em = COALESCE(publicado_em, NOW())
             WHERE id_estado = :estado"
        );
        $statement->bindValue('estado', $stateId, PDO::PARAM_INT);
        $statement->bindValue('atualizado_por', $userId, $userId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $statement->bindValue('revisado_por', $userId, $userId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $statement->bindValue('publicado_por', $userId, $userId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $statement->execute();

        return $statement->rowCount();
    }

    public function findByIbgeCode(int $code): ?array
    {
        $statement = Database::connection()->prepare('SELECT * FROM municipios WHERE codigo_ibge = :code LIMIT 1');
        $statement->execute(['code' => $code]);
        $municipality = $statement->fetch(PDO::FETCH_ASSOC);

        return $municipality ?: null;
    }

    public function upsertFromIbge(array $data): void
    {
        $existing = $this->findByIbgeCode((int) $data['codigo_ibge']);

        if ($existing !== null) {
            $manualFields = [
                'descricao_curta',
                'historia',
                'geografia',
                'economia',
                'cultura',
                'turismo',
                'educacao',
                'curiosidades',
                'populacao',
                'area',
                'densidade_demografica',
                'gentilico',
                'data_fundacao',
                'latitude',
                'longitude',
                'distancia_capital',
                'imagem_principal',
                'fonte_dados',
            ];
            foreach ($manualFields as $field) {
                if (!empty($existing[$field])) {
                    $data[$field] = $existing[$field];
                }
            }

            $data['status_curadoria'] = $existing['status_curadoria'];
            $data['status_publicacao'] = $existing['status_publicacao'];
            $data['criado_por'] = $existing['criado_por'];

            $this->update((int) $existing['id_municipio'], $data);
            return;
        }

        $this->create($data);
    }

    private function payload(array $data): array
    {
        $isPublished = ($data['status_publicacao'] ?? 'rascunho') === 'publicado';

        return [
            'id_estado' => (int) $data['id_estado'],
            'codigo_ibge' => (int) $data['codigo_ibge'],
            'nome' => $data['nome'],
            'slug' => $data['slug'],
            'descricao_curta' => $data['descricao_curta'] ?: null,
            'historia' => $data['historia'] ?: null,
            'geografia' => $data['geografia'] ?: null,
            'economia' => $data['economia'] ?: null,
            'cultura' => $data['cultura'] ?: null,
            'turismo' => $data['turismo'] ?: null,
            'educacao' => $data['educacao'] ?: null,
            'curiosidades' => $data['curiosidades'] ?: null,
            'populacao' => $data['populacao'] !== '' ? $data['populacao'] : null,
            'area' => $data['area'] !== '' ? $data['area'] : null,
            'densidade_demografica' => $data['densidade_demografica'] !== '' ? $data['densidade_demografica'] : null,
            'gentilico' => $data['gentilico'] ?: null,
            'data_fundacao' => $data['data_fundacao'] ?: null,
            'latitude' => $data['latitude'] !== '' ? $data['latitude'] : null,
            'longitude' => $data['longitude'] !== '' ? $data['longitude'] : null,
            'distancia_capital' => $data['distancia_capital'] !== '' ? $data['distancia_capital'] : null,
            'imagem_principal' => $data['imagem_principal'] ?: null,
            'fonte_dados' => $data['fonte_dados'] ?: null,
            'status_curadoria' => $data['status_curadoria'] ?? 'rascunho',
            'status_publicacao' => $data['status_publicacao'] ?? 'rascunho',
            'criado_por' => $data['criado_por'] ?? null,
            'atualizado_por' => $data['atualizado_por'] ?? null,
            'revisado_por' => $data['revisado_por'] ?? null,
            'publicado_por' => $isPublished ? ($data['publicado_por'] ?? null) : null,
            'publicado_em' => $isPublished ? ($data['publicado_em'] ?? date('Y-m-d H:i:s')) : null,
        ];
    }

    private function isPlaceholderContent(string $value): bool
    {
        $normalized = trim($value);
        if ($normalized === '') {
            return true;
        }

        $patterns = [
            'faz parte do acervo inicial do mapi conecta',
            'conteudo geografico inicial para',
            'conteudo cultural inicial para',
            'conteudo turistico inicial para',
            'conteudo ainda nao publicado nesta secao',
            'conteudo editorial em expansao para este municipio',
        ];

        $normalized = mb_strtolower($normalized, 'UTF-8');
        foreach ($patterns as $pattern) {
            if (str_contains($normalized, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function isAuthoredContent(string $value): bool
    {
        $normalized = trim($value);
        if ($normalized === '' || $this->isPlaceholderContent($normalized)) {
            return false;
        }

        $generatedMarkers = [
            'entra no MAPI CONECTA como ponto de partida',
            'esta secao deve evoluir com informacoes',
            'ainda sera aprofundada pela curadoria',
            'pode ser apresentada a partir de',
            'o potencial turistico de',
            'na frente educativa,',
            'esta secao pode reunir fatos pouco conhecidos',
            'a leitura economica inicial de',
        ];

        foreach ($generatedMarkers as $marker) {
            if (str_contains($normalized, $marker)) {
                return false;
            }
        }

        return true;
    }

    private function editorialSectionLabels(): array
    {
        return [
            'descricao_curta' => 'Descricao curta',
            'historia' => 'Historia',
            'geografia' => 'Geografia',
            'economia' => 'Economia',
            'cultura' => 'Cultura',
            'turismo' => 'Turismo',
            'educacao' => 'Educacao',
            'curiosidades' => 'Curiosidades',
        ];
    }

    private function factualFieldLabels(): array
    {
        return [
            'populacao' => 'Populacao',
            'area' => 'Area',
            'densidade_demografica' => 'Densidade',
            'gentilico' => 'Gentilico',
        ];
    }

    private function parseFactualEvidence(string $source): array
    {
        $source = trim($source);
        if ($source === '') {
            return [];
        }

        $segments = array_map('trim', explode(';', $source));
        foreach ($segments as $segment) {
            if (!str_starts_with($segment, 'IBGE Cidades e Estados|')) {
                continue;
            }

            $parts = explode('|', $segment);
            $evidence = [
                'fonte' => array_shift($parts),
            ];

            foreach ($parts as $part) {
                if (!str_contains($part, '=')) {
                    continue;
                }

                [$key, $value] = explode('=', $part, 2);
                $key = trim($key);
                $value = trim($value);
                if ($key === '' || $value === '') {
                    continue;
                }

                $evidence[$key] = $value;
            }

            return $evidence;
        }

        return ['fonte' => $source];
    }

    private function geoJsonMunicipalityCodes(): array
    {
        static $codes = null;

        if ($codes !== null) {
            return $codes;
        }

        $codes = [];
        $path = app_path('public/assets/data/piaui-municipios.geojson');
        if (!is_file($path) || !is_readable($path)) {
            return $codes;
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            return $codes;
        }

        $geoJson = json_decode($raw, true);
        if (!is_array($geoJson) || !isset($geoJson['features']) || !is_array($geoJson['features'])) {
            return $codes;
        }

        foreach ($geoJson['features'] as $feature) {
            $properties = is_array($feature['properties'] ?? null) ? $feature['properties'] : [];
            $code = preg_replace('/\D+/', '', (string) ($properties['codigo_ibge'] ?? $properties['id'] ?? $properties['code'] ?? ''));
            if ($code !== '') {
                $codes[$code] = true;
            }
        }

        return $codes;
    }
}
