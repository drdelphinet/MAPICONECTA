<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class User
{
    public function findByEmail(string $email): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT u.id_usuario AS id, u.nome, u.email, u.senha, u.status, p.slug AS role_slug, p.nome AS role_name
             FROM usuarios u
             INNER JOIN perfis p ON p.id_perfil = u.id_perfil
             WHERE u.email = :email
             LIMIT 1'
        );
        $statement->execute(['email' => $email]);

        $user = $statement->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT u.id_usuario AS id, u.nome, u.email, u.status, p.slug AS role_slug, p.nome AS role_name
             FROM usuarios u
             INNER JOIN perfis p ON p.id_perfil = u.id_perfil
             WHERE u.id_usuario = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);

        $user = $statement->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }
}
