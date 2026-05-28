<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class MunicipalityMedia
{
    private const TABLE = 'midias_municipio';
    private const VISUAL_CATEGORIES = [
        'paisagem',
        'patrimonio',
        'atrativo',
        'bandeira',
        'brasao',
        'cultura',
        'evento',
        'educacao',
        'documental',
    ];

    public static function visualCategories(): array
    {
        return self::VISUAL_CATEGORIES;
    }

    public function adminList(): array
    {
        if (!Database::tableExists(self::TABLE)) {
            return [];
        }

        $statement = Database::connection()->query(
            "SELECT mm.*, m.nome AS municipio_nome, m.slug AS municipio_slug, e.sigla AS estado_sigla
             FROM midias_municipio mm
             INNER JOIN municipios m ON m.id_municipio = mm.id_municipio
             INNER JOIN estados e ON e.id_estado = m.id_estado
             ORDER BY mm.atualizado_em DESC, mm.titulo ASC"
        );

        return array_map(fn (array $row): array => $this->normalizeRow($row), $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public function recentAdmin(int $limit = 5): array
    {
        if (!Database::tableExists(self::TABLE)) {
            return [];
        }
        $limit = max(1, $limit);

        $statement = Database::connection()->prepare(
            "SELECT mm.*, m.nome AS municipio_nome, e.sigla AS estado_sigla
             FROM midias_municipio mm
             INNER JOIN municipios m ON m.id_municipio = mm.id_municipio
             INNER JOIN estados e ON e.id_estado = m.id_estado
             ORDER BY mm.atualizado_em DESC, mm.titulo ASC
             LIMIT {$limit}"
        );
        $statement->execute();

        return array_map(fn (array $row): array => $this->normalizeRow($row), $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public function find(int $id): ?array
    {
        if (!Database::tableExists(self::TABLE)) {
            return null;
        }

        $statement = Database::connection()->prepare(
            "SELECT mm.*, m.nome AS municipio_nome, m.slug AS municipio_slug, e.sigla AS estado_sigla
             FROM midias_municipio mm
             INNER JOIN municipios m ON m.id_municipio = mm.id_municipio
             INNER JOIN estados e ON e.id_estado = m.id_estado
             WHERE mm.id_midia = :id
             LIMIT 1"
        );
        $statement->execute(['id' => $id]);

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->normalizeRow($row) : null;
    }

    public function publicByMunicipality(int $municipalityId, int $limit = 12): array
    {
        if (!Database::tableExists(self::TABLE)) {
            return [];
        }
        $limit = max(1, $limit);

        $statement = Database::connection()->prepare(
            "SELECT *
             FROM midias_municipio
             WHERE status_curadoria = 'publicado' AND id_municipio = :municipio
             ORDER BY destaque DESC, atualizado_em DESC, titulo ASC
             LIMIT {$limit}"
        );
        $statement->bindValue('municipio', $municipalityId, PDO::PARAM_INT);
        $statement->execute();

        return array_map(fn (array $row): array => $this->normalizeRow($row), $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findPublished(int $id): ?array
    {
        if (!Database::tableExists(self::TABLE)) {
            return null;
        }

        $statement = Database::connection()->prepare(
            "SELECT mm.*, m.nome AS municipio_nome, m.slug AS municipio_slug, e.sigla AS estado_sigla
             FROM midias_municipio mm
             INNER JOIN municipios m ON m.id_municipio = mm.id_municipio
             INNER JOIN estados e ON e.id_estado = m.id_estado
             WHERE mm.id_midia = :id
               AND mm.status_curadoria = 'publicado'
               AND m.status_publicacao = 'publicado'
             LIMIT 1"
        );
        $statement->execute(['id' => $id]);

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->normalizeRow($row) : null;
    }

    public function featuredPublic(int $limit = 4): array
    {
        if (!Database::tableExists(self::TABLE)) {
            return [];
        }
        $limit = max(1, $limit);

        $statement = Database::connection()->prepare(
            "SELECT mm.*, m.nome AS municipio_nome, m.slug AS municipio_slug, e.sigla AS estado_sigla
             FROM midias_municipio mm
             INNER JOIN municipios m ON m.id_municipio = mm.id_municipio
             INNER JOIN estados e ON e.id_estado = m.id_estado
             WHERE mm.status_curadoria = 'publicado'
               AND m.status_publicacao = 'publicado'
             ORDER BY mm.destaque DESC, mm.atualizado_em DESC, mm.titulo ASC
             LIMIT {$limit}"
        );
        $statement->execute();

        return array_map(fn (array $row): array => $this->normalizeRow($row), $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public function searchPublic(string $search, int $limit = 6): array
    {
        if (!Database::tableExists(self::TABLE)) {
            return [];
        }

        $search = trim($search);
        if ($search === '') {
            return [];
        }

        $limit = max(1, $limit);
        $statement = Database::connection()->prepare(
            "SELECT mm.id_midia, mm.titulo, mm.descricao, mm.tipo, mm.url_arquivo, m.nome AS municipio_nome, m.slug AS municipio_slug, e.sigla AS estado_sigla
             FROM midias_municipio mm
             INNER JOIN municipios m ON m.id_municipio = mm.id_municipio
             INNER JOIN estados e ON e.id_estado = m.id_estado
             WHERE mm.status_curadoria = 'publicado'
               AND m.status_publicacao = 'publicado'
               AND (
                    mm.titulo LIKE :search_titulo
                    OR mm.descricao LIKE :search_descricao
                    OR mm.tipo LIKE :search_tipo
                    OR m.nome LIKE :search_municipio
               )
             ORDER BY
                mm.destaque DESC,
                CASE WHEN mm.titulo LIKE :search_prefix THEN 0 ELSE 1 END,
                mm.titulo ASC
             LIMIT {$limit}"
        );
        $statement->execute([
            'search_titulo' => '%' . $search . '%',
            'search_descricao' => '%' . $search . '%',
            'search_tipo' => '%' . $search . '%',
            'search_municipio' => '%' . $search . '%',
            'search_prefix' => $search . '%',
        ]);

        return array_map(fn (array $row): array => $this->normalizeRow($row), $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public function create(array $data): int
    {
        if (!Database::tableExists(self::TABLE)) {
            return 0;
        }

        $payload = $this->payload($data);
        $columns = array_keys($payload);
        $statement = Database::connection()->prepare(
            'INSERT INTO ' . self::TABLE . ' (' . implode(', ', $columns) . ') VALUES (:' . implode(', :', $columns) . ')'
        );
        $statement->execute($payload);

        return (int) Database::connection()->lastInsertId();
    }

    public function findExistingImport(int $municipalityId, string $fileUrl, ?string $sourceUrl = null): ?array
    {
        if (!Database::tableExists(self::TABLE)) {
            return null;
        }

        $fileUrl = trim($fileUrl);
        $sourceUrl = trim((string) $sourceUrl);

        if ($fileUrl === '' && $sourceUrl === '') {
            return null;
        }

        $conditions = [];
        $params = [
            'municipio' => $municipalityId,
        ];

        if ($fileUrl !== '') {
            $conditions[] = 'url_arquivo = :file_url';
            $params['file_url'] = $fileUrl;
        }

        if ($sourceUrl !== '' && Database::columnExists(self::TABLE, 'url_origem')) {
            $conditions[] = 'url_origem = :source_url';
            $params['source_url'] = $sourceUrl;
        }

        if ($conditions === []) {
            return null;
        }

        $statement = Database::connection()->prepare(
            'SELECT *
             FROM ' . self::TABLE . '
             WHERE id_municipio = :municipio
               AND (' . implode(' OR ', $conditions) . ')
             ORDER BY id_midia DESC
             LIMIT 1'
        );
        $statement->execute($params);

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->normalizeRow($row) : null;
    }

    public function update(int $id, array $data): void
    {
        if (!Database::tableExists(self::TABLE)) {
            return;
        }

        $payload = $this->payload($data);
        $payload['id'] = $id;
        $assignments = [];
        foreach (array_keys($payload) as $column) {
            if ($column === 'id') {
                continue;
            }
            $assignments[] = $column . ' = :' . $column;
        }

        $statement = Database::connection()->prepare(
            'UPDATE ' . self::TABLE . ' SET ' . implode(', ', $assignments) . ' WHERE id_midia = :id'
        );
        $statement->execute($payload);
    }

    private function payload(array $data): array
    {
        $payload = [
            'id_municipio' => (int) $data['id_municipio'],
            'titulo' => $data['titulo'],
            'descricao' => $data['descricao'] ?: null,
            'tipo' => $data['tipo'],
            'url_arquivo' => $data['url_arquivo'],
            'credito' => $data['credito'] ?: null,
            'texto_alternativo' => $data['texto_alternativo'] ?: null,
            'destaque' => !empty($data['destaque']) ? 1 : 0,
            'status_curadoria' => $data['status_curadoria'],
            'criado_por' => $data['criado_por'] ?? null,
            'revisado_por' => $data['revisado_por'] ?? null,
            'publicado_por' => $data['publicado_por'] ?? null,
        ];

        if (Database::columnExists(self::TABLE, 'licenca')) {
            $payload['licenca'] = ($data['licenca'] ?? '') !== '' ? $data['licenca'] : null;
        }

        if (Database::columnExists(self::TABLE, 'url_origem')) {
            $payload['url_origem'] = ($data['url_origem'] ?? '') !== '' ? $data['url_origem'] : null;
        }

        if (Database::columnExists(self::TABLE, 'categoria_visual')) {
            $category = trim((string) ($data['categoria_visual'] ?? ''));
            $payload['categoria_visual'] = in_array($category, self::VISUAL_CATEGORIES, true) ? $category : null;
        }

        return $payload;
    }

    private function normalizeRow(array $row): array
    {
        $row['licenca'] = $row['licenca'] ?? null;
        $row['url_origem'] = $row['url_origem'] ?? null;
        $row['categoria_visual'] = $row['categoria_visual'] ?? null;

        return $row;
    }
}
