<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Quiz
{
    public function publicCount(): int
    {
        $statement = Database::connection()->query(
            "SELECT COUNT(*) FROM quizzes WHERE status_curadoria = 'publicado'"
        );

        return (int) $statement->fetchColumn();
    }

    public function featuredPublic(int $limit = 3): array
    {
        $limit = max(1, $limit);
        $statement = Database::connection()->prepare(
            "SELECT q.*, m.nome AS municipio_nome, m.slug AS municipio_slug
             FROM quizzes q
             INNER JOIN municipios m ON m.id_municipio = q.id_municipio
             WHERE q.status_curadoria = 'publicado'
             ORDER BY q.publicado_em DESC, q.titulo ASC
             LIMIT {$limit}"
        );
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function adminList(): array
    {
        $statement = Database::connection()->query(
            "SELECT q.*, m.nome AS municipio_nome, m.slug AS municipio_slug
             FROM quizzes q
             INNER JOIN municipios m ON m.id_municipio = q.id_municipio
             ORDER BY q.atualizado_em DESC, q.titulo ASC"
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function recentAdmin(int $limit = 5): array
    {
        $limit = max(1, $limit);
        $statement = Database::connection()->prepare(
            "SELECT q.*, m.nome AS municipio_nome, m.slug AS municipio_slug
             FROM quizzes q
             INNER JOIN municipios m ON m.id_municipio = q.id_municipio
             ORDER BY q.atualizado_em DESC, q.titulo ASC
             LIMIT {$limit}"
        );
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function publicByMunicipality(int $municipalityId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT q.*, COUNT(p.id_pergunta) AS total_perguntas
             FROM quizzes q
             LEFT JOIN perguntas_quiz p ON p.id_quiz = q.id_quiz AND p.status_curadoria = 'publicado'
             WHERE q.id_municipio = :municipio AND q.status_curadoria = 'publicado'
             GROUP BY q.id_quiz
             ORDER BY q.titulo ASC"
        );
        $statement->execute(['municipio' => $municipalityId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT q.*, m.nome AS municipio_nome, m.slug AS municipio_slug
             FROM quizzes q
             INNER JOIN municipios m ON m.id_municipio = q.id_municipio
             WHERE q.id_quiz = :id
             LIMIT 1"
        );
        $statement->execute(['id' => $id]);
        $quiz = $statement->fetch(PDO::FETCH_ASSOC);

        return $quiz ?: null;
    }

    public function findPublished(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT q.*, m.nome AS municipio_nome, m.slug AS municipio_slug
             FROM quizzes q
             INNER JOIN municipios m ON m.id_municipio = q.id_municipio
             WHERE q.id_quiz = :id AND q.status_curadoria = 'publicado'
             LIMIT 1"
        );
        $statement->execute(['id' => $id]);
        $quiz = $statement->fetch(PDO::FETCH_ASSOC);

        return $quiz ?: null;
    }

    public function create(array $data): int
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO quizzes (id_municipio, titulo, descricao, status_curadoria, criado_por, revisado_por, publicado_por, publicado_em)
             VALUES (:id_municipio, :titulo, :descricao, :status_curadoria, :criado_por, :revisado_por, :publicado_por, :publicado_em)'
        );
        $statement->execute([
            'id_municipio' => $data['id_municipio'],
            'titulo' => $data['titulo'],
            'descricao' => $data['descricao'] ?: null,
            'status_curadoria' => $data['status_curadoria'],
            'criado_por' => $data['criado_por'] ?? null,
            'revisado_por' => $data['revisado_por'] ?? null,
            'publicado_por' => $data['publicado_por'] ?? null,
            'publicado_em' => $data['publicado_em'] ?? null,
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $statement = Database::connection()->prepare(
            'UPDATE quizzes
             SET id_municipio = :id_municipio,
                 titulo = :titulo,
                 descricao = :descricao,
                 status_curadoria = :status_curadoria,
                 revisado_por = :revisado_por,
                 publicado_por = :publicado_por,
                 publicado_em = :publicado_em
             WHERE id_quiz = :id'
        );
        $statement->execute([
            'id' => $id,
            'id_municipio' => $data['id_municipio'],
            'titulo' => $data['titulo'],
            'descricao' => $data['descricao'] ?: null,
            'status_curadoria' => $data['status_curadoria'],
            'revisado_por' => $data['revisado_por'] ?? null,
            'publicado_por' => $data['publicado_por'] ?? null,
            'publicado_em' => $data['publicado_em'] ?? null,
        ]);
    }
}
