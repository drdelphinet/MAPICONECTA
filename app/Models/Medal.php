<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Medal
{
    public function allByThreshold(): array
    {
        $statement = Database::connection()->query('SELECT * FROM medalhas ORDER BY pontos_necessarios ASC, id_medalha ASC');

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function awardToUser(int $userId, int $medalId): void
    {
        $statement = Database::connection()->prepare(
            'INSERT IGNORE INTO usuarios_medalhas (id_usuario, id_medalha) VALUES (:usuario, :medalha)'
        );
        $statement->execute([
            'usuario' => $userId,
            'medalha' => $medalId,
        ]);
    }

    public function countForUser(int $userId): int
    {
        $statement = Database::connection()->prepare('SELECT COUNT(*) FROM usuarios_medalhas WHERE id_usuario = :usuario');
        $statement->execute(['usuario' => $userId]);

        return (int) $statement->fetchColumn();
    }

    public function listForUser(int $userId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT m.*, um.conquistada_em
             FROM usuarios_medalhas um
             INNER JOIN medalhas m ON m.id_medalha = um.id_medalha
             WHERE um.id_usuario = :usuario
             ORDER BY um.conquistada_em DESC'
        );
        $statement->execute(['usuario' => $userId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
