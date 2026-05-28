<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class CurationHistory
{
    public function create(array $data): void
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO historico_curadoria (entidade_tipo, entidade_id, acao, status_anterior, status_novo, observacao, usuario_id)
             VALUES (:entidade_tipo, :entidade_id, :acao, :status_anterior, :status_novo, :observacao, :usuario_id)'
        );

        $statement->execute([
            'entidade_tipo' => $data['entidade_tipo'],
            'entidade_id' => $data['entidade_id'],
            'acao' => $data['acao'],
            'status_anterior' => $data['status_anterior'] ?? null,
            'status_novo' => $data['status_novo'] ?? null,
            'observacao' => $data['observacao'] ?? null,
            'usuario_id' => $data['usuario_id'] ?? null,
        ]);
    }

    public function byEntity(string $entityType, int $entityId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT h.*, u.nome AS usuario_nome
             FROM historico_curadoria h
             LEFT JOIN usuarios u ON u.id_usuario = h.usuario_id
             WHERE h.entidade_tipo = :entidade_tipo AND h.entidade_id = :entidade_id
             ORDER BY h.criado_em DESC, h.id_historico DESC'
        );
        $statement->execute([
            'entidade_tipo' => $entityType,
            'entidade_id' => $entityId,
        ]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
