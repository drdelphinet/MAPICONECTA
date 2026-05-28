<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class QuizQuestion
{
    public function forQuizAdmin(int $quizId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT * FROM perguntas_quiz WHERE id_quiz = :quiz ORDER BY ordem ASC, id_pergunta ASC'
        );
        $statement->execute(['quiz' => $quizId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function forQuizPublic(int $quizId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT * FROM perguntas_quiz
             WHERE id_quiz = :quiz AND status_curadoria = 'publicado'
             ORDER BY ordem ASC, id_pergunta ASC"
        );
        $statement->execute(['quiz' => $quizId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $statement = Database::connection()->prepare('SELECT * FROM perguntas_quiz WHERE id_pergunta = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $question = $statement->fetch(PDO::FETCH_ASSOC);

        return $question ?: null;
    }

    public function create(array $data): int
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO perguntas_quiz (
                id_quiz, pergunta, alternativa_a, alternativa_b, alternativa_c, alternativa_d,
                resposta_correta, explicacao, pontos, ordem, status_curadoria, criado_por, revisado_por, publicado_por
             ) VALUES (
                :id_quiz, :pergunta, :alternativa_a, :alternativa_b, :alternativa_c, :alternativa_d,
                :resposta_correta, :explicacao, :pontos, :ordem, :status_curadoria, :criado_por, :revisado_por, :publicado_por
             )'
        );
        $statement->execute([
            'id_quiz' => $data['id_quiz'],
            'pergunta' => $data['pergunta'],
            'alternativa_a' => $data['alternativa_a'],
            'alternativa_b' => $data['alternativa_b'],
            'alternativa_c' => $data['alternativa_c'],
            'alternativa_d' => $data['alternativa_d'],
            'resposta_correta' => $data['resposta_correta'],
            'explicacao' => $data['explicacao'] ?: null,
            'pontos' => $data['pontos'],
            'ordem' => $data['ordem'],
            'status_curadoria' => $data['status_curadoria'],
            'criado_por' => $data['criado_por'] ?? null,
            'revisado_por' => $data['revisado_por'] ?? null,
            'publicado_por' => $data['publicado_por'] ?? null,
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $statement = Database::connection()->prepare(
            'UPDATE perguntas_quiz
             SET pergunta = :pergunta,
                 alternativa_a = :alternativa_a,
                 alternativa_b = :alternativa_b,
                 alternativa_c = :alternativa_c,
                 alternativa_d = :alternativa_d,
                 resposta_correta = :resposta_correta,
                 explicacao = :explicacao,
                 pontos = :pontos,
                 ordem = :ordem,
                 status_curadoria = :status_curadoria,
                 revisado_por = :revisado_por,
                 publicado_por = :publicado_por
             WHERE id_pergunta = :id'
        );
        $statement->execute([
            'id' => $id,
            'pergunta' => $data['pergunta'],
            'alternativa_a' => $data['alternativa_a'],
            'alternativa_b' => $data['alternativa_b'],
            'alternativa_c' => $data['alternativa_c'],
            'alternativa_d' => $data['alternativa_d'],
            'resposta_correta' => $data['resposta_correta'],
            'explicacao' => $data['explicacao'] ?: null,
            'pontos' => $data['pontos'],
            'ordem' => $data['ordem'],
            'status_curadoria' => $data['status_curadoria'],
            'revisado_por' => $data['revisado_por'] ?? null,
            'publicado_por' => $data['publicado_por'] ?? null,
        ]);
    }
}
