<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class IntegrationLog
{
    public function create(array $data): void
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO integracao_logs (fonte, endpoint, parametros, status, mensagem, resposta_resumida, usuario_id)
             VALUES (:fonte, :endpoint, :parametros, :status, :mensagem, :resposta_resumida, :usuario_id)'
        );

        $statement->execute([
            'fonte' => $data['fonte'],
            'endpoint' => $data['endpoint'],
            'parametros' => $data['parametros'] !== null ? json_encode($data['parametros'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            'status' => $data['status'],
            'mensagem' => $data['mensagem'] ?? null,
            'resposta_resumida' => $data['resposta_resumida'] ?? null,
            'usuario_id' => $data['usuario_id'] ?? null,
        ]);
    }

    public function latest(int $limit = 20): array
    {
        $statement = Database::connection()->prepare('SELECT * FROM integracao_logs ORDER BY data_execucao DESC LIMIT :limit');
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function latestBySource(string $source, int $limit = 20, ?string $endpoint = null): array
    {
        $sql = 'SELECT * FROM integracao_logs WHERE fonte = :fonte';
        if ($endpoint !== null && $endpoint !== '') {
            $sql .= ' AND endpoint = :endpoint';
        }
        $sql .= ' ORDER BY data_execucao DESC LIMIT :limit';

        $statement = Database::connection()->prepare($sql);
        $statement->bindValue('fonte', $source);
        if ($endpoint !== null && $endpoint !== '') {
            $statement->bindValue('endpoint', $endpoint);
        }
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
