<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class State
{
    public function all(): array
    {
        $statement = Database::connection()->query('SELECT * FROM estados ORDER BY nome ASC');

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function activeForSelect(): array
    {
        $statement = Database::connection()->query("SELECT id_estado, nome, sigla, slug FROM estados WHERE status IN ('ativo', 'rascunho') ORDER BY nome ASC");

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $statement = Database::connection()->prepare('SELECT * FROM estados WHERE id_estado = :id LIMIT 1');
        $statement->execute(['id' => $id]);

        $state = $statement->fetch(PDO::FETCH_ASSOC);

        return $state ?: null;
    }

    public function create(array $data): int
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO estados (codigo_ibge, nome, sigla, regiao, slug, status)
             VALUES (:codigo_ibge, :nome, :sigla, :regiao, :slug, :status)'
        );

        $statement->execute([
            'codigo_ibge' => $data['codigo_ibge'],
            'nome' => $data['nome'],
            'sigla' => strtoupper($data['sigla']),
            'regiao' => $data['regiao'],
            'slug' => $data['slug'],
            'status' => $data['status'],
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $statement = Database::connection()->prepare(
            'UPDATE estados
             SET codigo_ibge = :codigo_ibge, nome = :nome, sigla = :sigla, regiao = :regiao, slug = :slug, status = :status
             WHERE id_estado = :id'
        );

        $statement->execute([
            'id' => $id,
            'codigo_ibge' => $data['codigo_ibge'],
            'nome' => $data['nome'],
            'sigla' => strtoupper($data['sigla']),
            'regiao' => $data['regiao'],
            'slug' => $data['slug'],
            'status' => $data['status'],
        ]);
    }

    public function findByCodeOrSigla(string $codeOrSigla): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT * FROM estados WHERE codigo_ibge = :code OR sigla = :sigla LIMIT 1'
        );
        $statement->execute([
            'code' => (int) $codeOrSigla,
            'sigla' => strtoupper($codeOrSigla),
        ]);

        $state = $statement->fetch(PDO::FETCH_ASSOC);

        return $state ?: null;
    }

    public function upsertFromIbge(array $data): void
    {
        $existing = $this->findByCodeOrSigla((string) $data['codigo_ibge']);

        if ($existing) {
            $this->update((int) $existing['id_estado'], [
                'codigo_ibge' => $data['codigo_ibge'],
                'nome' => $data['nome'],
                'sigla' => $data['sigla'],
                'regiao' => $data['regiao'],
                'slug' => $data['slug'],
                'status' => $existing['status'] ?? 'ativo',
            ]);

            return;
        }

        $this->create($data);
    }
}
