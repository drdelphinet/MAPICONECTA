<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class PointOfInterest
{
    public function adminList(): array
    {
        $statement = Database::connection()->query(
            "SELECT p.*, m.nome AS municipio_nome, m.slug AS municipio_slug, e.sigla AS estado_sigla
             FROM pontos_turisticos p
             INNER JOIN municipios m ON m.id_municipio = p.id_municipio
             INNER JOIN estados e ON e.id_estado = m.id_estado
             ORDER BY p.atualizado_em DESC, p.nome ASC"
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function recentAdmin(int $limit = 5): array
    {
        $limit = max(1, $limit);
        $statement = Database::connection()->prepare(
            "SELECT p.*, m.nome AS municipio_nome, e.sigla AS estado_sigla
             FROM pontos_turisticos p
             INNER JOIN municipios m ON m.id_municipio = p.id_municipio
             INNER JOIN estados e ON e.id_estado = m.id_estado
             ORDER BY p.atualizado_em DESC, p.nome ASC
             LIMIT {$limit}"
        );
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT p.*, m.nome AS municipio_nome, m.slug AS municipio_slug, e.sigla AS estado_sigla
             FROM pontos_turisticos p
             INNER JOIN municipios m ON m.id_municipio = p.id_municipio
             INNER JOIN estados e ON e.id_estado = m.id_estado
             WHERE p.id_ponto_turistico = :id
             LIMIT 1"
        );
        $statement->execute(['id' => $id]);

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function findPublished(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT p.*, m.nome AS municipio_nome, m.slug AS municipio_slug, e.sigla AS estado_sigla
             FROM pontos_turisticos p
             INNER JOIN municipios m ON m.id_municipio = p.id_municipio
             INNER JOIN estados e ON e.id_estado = m.id_estado
             WHERE p.id_ponto_turistico = :id
               AND p.status_curadoria = 'publicado'
               AND m.status_publicacao = 'publicado'
             LIMIT 1"
        );
        $statement->execute(['id' => $id]);

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function publicByMunicipality(int $municipalityId, int $limit = 12): array
    {
        $limit = max(1, $limit);
        $statement = Database::connection()->prepare(
            "SELECT *
             FROM pontos_turisticos
             WHERE status_curadoria = 'publicado' AND id_municipio = :municipio
             ORDER BY nome ASC
             LIMIT {$limit}"
        );
        $statement->bindValue('municipio', $municipalityId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function featuredPublic(int $limit = 3): array
    {
        $limit = max(1, $limit);
        $statement = Database::connection()->prepare(
            "SELECT p.*, m.nome AS municipio_nome, m.slug AS municipio_slug, e.sigla AS estado_sigla
             FROM pontos_turisticos p
             INNER JOIN municipios m ON m.id_municipio = p.id_municipio
             INNER JOIN estados e ON e.id_estado = m.id_estado
             WHERE p.status_curadoria = 'publicado'
               AND m.status_publicacao = 'publicado'
             ORDER BY p.atualizado_em DESC, p.nome ASC
             LIMIT {$limit}"
        );
        $statement->execute();

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
            "SELECT p.id_ponto_turistico, p.nome, p.categoria, p.descricao, m.nome AS municipio_nome, m.slug AS municipio_slug, e.sigla AS estado_sigla
             FROM pontos_turisticos p
             INNER JOIN municipios m ON m.id_municipio = p.id_municipio
             INNER JOIN estados e ON e.id_estado = m.id_estado
             WHERE p.status_curadoria = 'publicado'
               AND m.status_publicacao = 'publicado'
               AND (
                    p.nome LIKE :search_nome
                    OR p.descricao LIKE :search_descricao
                    OR p.categoria LIKE :search_categoria
                    OR m.nome LIKE :search_municipio
               )
             ORDER BY
                CASE WHEN p.nome LIKE :search_prefix THEN 0 ELSE 1 END,
                p.nome ASC
             LIMIT {$limit}"
        );
        $statement->execute([
            'search_nome' => '%' . $search . '%',
            'search_descricao' => '%' . $search . '%',
            'search_categoria' => '%' . $search . '%',
            'search_municipio' => '%' . $search . '%',
            'search_prefix' => $search . '%',
        ]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): int
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO pontos_turisticos (
                id_municipio, nome, categoria, descricao, endereco, latitude, longitude,
                horario_funcionamento, valor_entrada, contato, imagem, fonte, status_curadoria,
                criado_por, revisado_por, publicado_por
            ) VALUES (
                :id_municipio, :nome, :categoria, :descricao, :endereco, :latitude, :longitude,
                :horario_funcionamento, :valor_entrada, :contato, :imagem, :fonte, :status_curadoria,
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
            'UPDATE pontos_turisticos SET
                id_municipio = :id_municipio,
                nome = :nome,
                categoria = :categoria,
                descricao = :descricao,
                endereco = :endereco,
                latitude = :latitude,
                longitude = :longitude,
                horario_funcionamento = :horario_funcionamento,
                valor_entrada = :valor_entrada,
                contato = :contato,
                imagem = :imagem,
                fonte = :fonte,
                status_curadoria = :status_curadoria,
                revisado_por = :revisado_por,
                publicado_por = :publicado_por
             WHERE id_ponto_turistico = :id'
        );
        $statement->execute($payload);
    }

    private function payload(array $data): array
    {
        return [
            'id_municipio' => (int) $data['id_municipio'],
            'nome' => $data['nome'],
            'categoria' => $data['categoria'] ?: null,
            'descricao' => $data['descricao'] ?: null,
            'endereco' => $data['endereco'] ?: null,
            'latitude' => $data['latitude'] !== '' ? $data['latitude'] : null,
            'longitude' => $data['longitude'] !== '' ? $data['longitude'] : null,
            'horario_funcionamento' => $data['horario_funcionamento'] ?: null,
            'valor_entrada' => $data['valor_entrada'] ?: null,
            'contato' => $data['contato'] ?: null,
            'imagem' => $data['imagem'] ?: null,
            'fonte' => $data['fonte'] ?: null,
            'status_curadoria' => $data['status_curadoria'],
            'criado_por' => $data['criado_por'] ?? null,
            'revisado_por' => $data['revisado_por'] ?? null,
            'publicado_por' => $data['publicado_por'] ?? null,
        ];
    }
}
