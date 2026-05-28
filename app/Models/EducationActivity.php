<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class EducationActivity
{
    public function adminList(): array
    {
        $statement = Database::connection()->query(
            "SELECT a.*, m.nome AS municipio_nome, m.slug AS municipio_slug, e.sigla AS estado_sigla
             FROM atividades_pedagogicas a
             INNER JOIN municipios m ON m.id_municipio = a.id_municipio
             INNER JOIN estados e ON e.id_estado = m.id_estado
             ORDER BY a.atualizado_em DESC, a.titulo ASC"
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function recentAdmin(int $limit = 5): array
    {
        $limit = max(1, $limit);
        $statement = Database::connection()->prepare(
            "SELECT a.*, m.nome AS municipio_nome, m.slug AS municipio_slug, e.sigla AS estado_sigla
             FROM atividades_pedagogicas a
             INNER JOIN municipios m ON m.id_municipio = a.id_municipio
             INNER JOIN estados e ON e.id_estado = m.id_estado
             ORDER BY a.atualizado_em DESC, a.titulo ASC
             LIMIT {$limit}"
        );
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT a.*, m.nome AS municipio_nome, m.slug AS municipio_slug, e.sigla AS estado_sigla
             FROM atividades_pedagogicas a
             INNER JOIN municipios m ON m.id_municipio = a.id_municipio
             INNER JOIN estados e ON e.id_estado = m.id_estado
             WHERE a.id_atividade = :id
             LIMIT 1"
        );
        $statement->execute(['id' => $id]);

        $activity = $statement->fetch(PDO::FETCH_ASSOC);

        return $activity ?: null;
    }

    public function findPublished(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT a.*, m.nome AS municipio_nome, m.slug AS municipio_slug, e.sigla AS estado_sigla
             FROM atividades_pedagogicas a
             INNER JOIN municipios m ON m.id_municipio = a.id_municipio
             INNER JOIN estados e ON e.id_estado = m.id_estado
             WHERE a.id_atividade = :id
               AND a.status_curadoria = 'publicado'
               AND m.status_publicacao = 'publicado'
             LIMIT 1"
        );
        $statement->execute(['id' => $id]);

        $activity = $statement->fetch(PDO::FETCH_ASSOC);

        return $activity ?: null;
    }

    public function publicByMunicipality(int $municipalityId, int $limit = 3): array
    {
        $limit = max(1, $limit);
        $statement = Database::connection()->prepare(
            "SELECT a.*, m.nome AS municipio_nome, m.slug AS municipio_slug, e.sigla AS estado_sigla
             FROM atividades_pedagogicas a
             INNER JOIN municipios m ON m.id_municipio = a.id_municipio
             INNER JOIN estados e ON e.id_estado = m.id_estado
             WHERE a.status_curadoria = 'publicado' AND a.id_municipio = :municipio
             ORDER BY a.titulo ASC
             LIMIT {$limit}"
        );
        $statement->bindValue('municipio', $municipalityId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function publicList(?int $municipalityId = null, string $discipline = '', string $schoolYear = ''): array
    {
        $sql = "SELECT a.*, m.nome AS municipio_nome, m.slug AS municipio_slug, m.estado_sigla
                FROM atividades_pedagogicas a
                INNER JOIN (
                    SELECT m.id_municipio, m.nome, m.slug, e.sigla AS estado_sigla
                    FROM municipios m
                    INNER JOIN estados e ON e.id_estado = m.id_estado
                    WHERE m.status_publicacao = 'publicado'
                ) m ON m.id_municipio = a.id_municipio
                WHERE a.status_curadoria = 'publicado'";
        $params = [];

        if ($municipalityId !== null) {
            $sql .= ' AND a.id_municipio = :municipio';
            $params['municipio'] = $municipalityId;
        }

        if ($discipline !== '') {
            $sql .= ' AND a.disciplina = :disciplina';
            $params['disciplina'] = $discipline;
        }

        if ($schoolYear !== '') {
            $sql .= ' AND a.ano_escolar = :ano_escolar';
            $params['ano_escolar'] = $schoolYear;
        }

        $sql .= ' ORDER BY a.titulo ASC';

        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchPublic(string $search, int $limit = 6): array
    {
        $search = trim($search);
        if ($search === '') {
            return [];
        }

        $limit = max(1, $limit);
        $statement = Database::connection()->prepare(
            "SELECT a.id_atividade, a.titulo, a.descricao, a.disciplina, a.ano_escolar, m.nome AS municipio_nome, m.slug AS municipio_slug, e.sigla AS estado_sigla
             FROM atividades_pedagogicas a
             INNER JOIN municipios m ON m.id_municipio = a.id_municipio
             INNER JOIN estados e ON e.id_estado = m.id_estado
             WHERE a.status_curadoria = 'publicado'
               AND m.status_publicacao = 'publicado'
               AND (
                    a.titulo LIKE :search_titulo
                    OR a.descricao LIKE :search_descricao
                    OR a.disciplina LIKE :search_disciplina
                    OR a.objetivo LIKE :search_objetivo
                    OR m.nome LIKE :search_municipio
               )
             ORDER BY
                CASE WHEN a.titulo LIKE :search_prefix THEN 0 ELSE 1 END,
                a.titulo ASC
             LIMIT {$limit}"
        );
        $statement->execute([
            'search_titulo' => '%' . $search . '%',
            'search_descricao' => '%' . $search . '%',
            'search_disciplina' => '%' . $search . '%',
            'search_objetivo' => '%' . $search . '%',
            'search_municipio' => '%' . $search . '%',
            'search_prefix' => $search . '%',
        ]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function filterOptions(): array
    {
        return [
            'disciplinas' => $this->distinctValues('disciplina'),
            'anos' => $this->distinctValues('ano_escolar'),
        ];
    }

    public function create(array $data): int
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO atividades_pedagogicas (
                id_municipio, titulo, descricao, disciplina, ano_escolar, objetivo, metodologia,
                recursos_necessarios, desenvolvimento, avaliacao, arquivo_pdf, status_curadoria,
                criado_por, revisado_por, publicado_por
             ) VALUES (
                :id_municipio, :titulo, :descricao, :disciplina, :ano_escolar, :objetivo, :metodologia,
                :recursos_necessarios, :desenvolvimento, :avaliacao, :arquivo_pdf, :status_curadoria,
                :criado_por, :revisado_por, :publicado_por
             )'
        );
        $statement->execute($this->payload($data));

        return (int) Database::connection()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $payload = $this->payload($data);
        $payload['id'] = $id;

        $statement = Database::connection()->prepare(
            'UPDATE atividades_pedagogicas SET
                id_municipio = :id_municipio,
                titulo = :titulo,
                descricao = :descricao,
                disciplina = :disciplina,
                ano_escolar = :ano_escolar,
                objetivo = :objetivo,
                metodologia = :metodologia,
                recursos_necessarios = :recursos_necessarios,
                desenvolvimento = :desenvolvimento,
                avaliacao = :avaliacao,
                arquivo_pdf = :arquivo_pdf,
                status_curadoria = :status_curadoria,
                revisado_por = :revisado_por,
                publicado_por = :publicado_por
             WHERE id_atividade = :id'
        );
        $statement->execute($payload);
    }

    private function distinctValues(string $field): array
    {
        $allowed = ['disciplina', 'ano_escolar'];
        if (!in_array($field, $allowed, true)) {
            return [];
        }

        $statement = Database::connection()->query(
            "SELECT DISTINCT {$field}
             FROM atividades_pedagogicas
             WHERE status_curadoria = 'publicado' AND {$field} IS NOT NULL AND {$field} <> ''
             ORDER BY {$field} ASC"
        );

        return array_values(array_filter($statement->fetchAll(PDO::FETCH_COLUMN), static fn ($value) => $value !== null && $value !== ''));
    }

    private function payload(array $data): array
    {
        return [
            'id_municipio' => (int) $data['id_municipio'],
            'titulo' => $data['titulo'],
            'descricao' => $data['descricao'] ?: null,
            'disciplina' => $data['disciplina'],
            'ano_escolar' => $data['ano_escolar'] ?: null,
            'objetivo' => $data['objetivo'] ?: null,
            'metodologia' => $data['metodologia'] ?: null,
            'recursos_necessarios' => $data['recursos_necessarios'] ?: null,
            'desenvolvimento' => $data['desenvolvimento'] ?: null,
            'avaliacao' => $data['avaliacao'] ?: null,
            'arquivo_pdf' => $data['arquivo_pdf'] ?: null,
            'status_curadoria' => $data['status_curadoria'],
            'criado_por' => $data['criado_por'] ?? null,
            'revisado_por' => $data['revisado_por'] ?? null,
            'publicado_por' => $data['publicado_por'] ?? null,
        ];
    }
}
