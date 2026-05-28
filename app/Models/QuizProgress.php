<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class QuizProgress
{
    public function upsertResult(int $userId, int $quizId, int $score, int $correct, int $total): void
    {
        $existing = $this->findByUserAndQuiz($userId, $quizId);

        if ($existing) {
            $statement = Database::connection()->prepare(
                'UPDATE progresso_quiz_usuario
                 SET pontuacao_total = :pontuacao_total,
                     total_acertos = :total_acertos,
                     total_perguntas = :total_perguntas,
                     concluido_em = :concluido_em
                 WHERE id_progresso = :id'
            );
            $statement->execute([
                'id' => $existing['id_progresso'],
                'pontuacao_total' => $score,
                'total_acertos' => $correct,
                'total_perguntas' => $total,
                'concluido_em' => date('Y-m-d H:i:s'),
            ]);
            return;
        }

        $statement = Database::connection()->prepare(
            'INSERT INTO progresso_quiz_usuario (id_usuario, id_quiz, pontuacao_total, total_acertos, total_perguntas, concluido_em)
             VALUES (:id_usuario, :id_quiz, :pontuacao_total, :total_acertos, :total_perguntas, :concluido_em)'
        );
        $statement->execute([
            'id_usuario' => $userId,
            'id_quiz' => $quizId,
            'pontuacao_total' => $score,
            'total_acertos' => $correct,
            'total_perguntas' => $total,
            'concluido_em' => date('Y-m-d H:i:s'),
        ]);
    }

    public function replaceAnswers(int $userId, int $quizId, array $answers): void
    {
        $delete = Database::connection()->prepare('DELETE FROM respostas_quiz_usuario WHERE id_usuario = :usuario AND id_quiz = :quiz');
        $delete->execute([
            'usuario' => $userId,
            'quiz' => $quizId,
        ]);

        $statement = Database::connection()->prepare(
            'INSERT INTO respostas_quiz_usuario (
                id_usuario, id_quiz, id_pergunta, resposta_marcada, resposta_correta, acertou, pontos_obtidos
             ) VALUES (
                :id_usuario, :id_quiz, :id_pergunta, :resposta_marcada, :resposta_correta, :acertou, :pontos_obtidos
             )'
        );

        foreach ($answers as $answer) {
            $statement->execute([
                'id_usuario' => $userId,
                'id_quiz' => $quizId,
                'id_pergunta' => $answer['id_pergunta'],
                'resposta_marcada' => $answer['resposta_marcada'],
                'resposta_correta' => $answer['resposta_correta'],
                'acertou' => $answer['acertou'] ? 1 : 0,
                'pontos_obtidos' => $answer['pontos_obtidos'],
            ]);
        }
    }

    public function ranking(int $limit = 10): array
    {
        $statement = Database::connection()->prepare(
            'SELECT u.id_usuario, u.nome, SUM(p.pontuacao_total) AS pontuacao_total, COUNT(*) AS quizzes_respondidos
             FROM progresso_quiz_usuario p
             INNER JOIN usuarios u ON u.id_usuario = p.id_usuario
             GROUP BY u.id_usuario, u.nome
             ORDER BY pontuacao_total DESC, quizzes_respondidos DESC, u.nome ASC
             LIMIT :limit'
        );
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function userStats(int $userId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT
                COALESCE(SUM(pontuacao_total), 0) AS pontuacao_total,
                COUNT(*) AS quizzes_respondidos,
                COALESCE(SUM(total_acertos), 0) AS total_acertos
             FROM progresso_quiz_usuario
             WHERE id_usuario = :usuario'
        );
        $statement->execute(['usuario' => $userId]);

        return $statement->fetch(PDO::FETCH_ASSOC) ?: [
            'pontuacao_total' => 0,
            'quizzes_respondidos' => 0,
            'total_acertos' => 0,
        ];
    }

    public function userHistory(int $userId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT p.*, q.titulo AS quiz_titulo, m.nome AS municipio_nome
             FROM progresso_quiz_usuario p
             INNER JOIN quizzes q ON q.id_quiz = p.id_quiz
             INNER JOIN municipios m ON m.id_municipio = q.id_municipio
             WHERE p.id_usuario = :usuario
             ORDER BY p.concluido_em DESC, p.atualizado_em DESC'
        );
        $statement->execute(['usuario' => $userId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function findByUserAndQuiz(int $userId, int $quizId): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT * FROM progresso_quiz_usuario WHERE id_usuario = :usuario AND id_quiz = :quiz LIMIT 1'
        );
        $statement->execute([
            'usuario' => $userId,
            'quiz' => $quizId,
        ]);

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }
}
