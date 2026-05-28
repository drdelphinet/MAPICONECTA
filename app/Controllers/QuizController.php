<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Request;
use App\Models\Medal;
use App\Models\Municipality;
use App\Models\Quiz;
use App\Models\QuizProgress;
use App\Models\QuizQuestion;
use App\Services\GamificationService;
use RuntimeException;

final class QuizController extends Controller
{
    public function __construct(
        private readonly Quiz $quizzes = new Quiz(),
        private readonly QuizQuestion $questions = new QuizQuestion(),
        private readonly QuizProgress $progress = new QuizProgress(),
        private readonly GamificationService $gamification = new GamificationService(),
        private readonly Medal $medals = new Medal(),
        private readonly Municipality $municipalities = new Municipality()
    ) {
    }

    public function show(Request $request): void
    {
        $quiz = $this->quizzes->findPublished((int) $request->route('id'));
        if ($quiz === null) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Quiz nao encontrado']);
            return;
        }

        $this->view('quizzes/show', [
            'title' => $quiz['titulo'],
            'user' => Auth::user(),
            'quiz' => $quiz,
            'questions' => $this->questions->forQuizPublic((int) $quiz['id_quiz']),
        ]);
    }

    public function submit(Request $request): void
    {
        try {
            Csrf::validate((string) $request->input('_token'));
        } catch (RuntimeException $exception) {
            $this->redirectWithMessage('', 'error', 'Sua sessao expirou. Tente responder o quiz novamente.');
        }

        $quiz = $this->quizzes->findPublished((int) $request->route('id'));
        if ($quiz === null) {
            $this->redirectWithMessage('', 'error', 'Quiz nao encontrado.');
        }

        $questions = $this->questions->forQuizPublic((int) $quiz['id_quiz']);
        $results = [];
        $score = 0;
        $correct = 0;

        foreach ($questions as $question) {
            $answer = (string) $request->input('pergunta_' . $question['id_pergunta'], '');
            $isCorrect = $answer !== '' && $answer === $question['resposta_correta'];
            $points = $isCorrect ? (int) $question['pontos'] : 0;

            $results[] = [
                'id_pergunta' => (int) $question['id_pergunta'],
                'pergunta' => $question['pergunta'],
                'resposta_marcada' => $answer,
                'resposta_correta' => $question['resposta_correta'],
                'explicacao' => $question['explicacao'],
                'acertou' => $isCorrect,
                'pontos_obtidos' => $points,
                'alternativas' => [
                    'a' => $question['alternativa_a'],
                    'b' => $question['alternativa_b'],
                    'c' => $question['alternativa_c'],
                    'd' => $question['alternativa_d'],
                ],
            ];

            if ($isCorrect) {
                $correct++;
                $score += $points;
            }
        }

        $awardedMedals = [];

        if (Auth::check()) {
            $user = Auth::user();
            $this->progress->upsertResult((int) $user['id'], (int) $quiz['id_quiz'], $score, $correct, count($questions));
            $this->progress->replaceAnswers((int) $user['id'], (int) $quiz['id_quiz'], $results);
            $this->gamification->syncUserMedals((int) $user['id']);
            $awardedMedals = $this->medals->listForUser((int) $user['id']);
        }

        $this->view('quizzes/result', [
            'title' => 'Resultado do quiz',
            'user' => Auth::user(),
            'quiz' => $quiz,
            'results' => $results,
            'score' => $score,
            'correct' => $correct,
            'total' => count($questions),
            'saved' => Auth::check(),
            'awardedMedals' => $awardedMedals,
        ]);
    }

    public function ranking(Request $request): void
    {
        $this->view('quizzes/ranking', [
            'title' => 'Ranking',
            'user' => Auth::user(),
            'ranking' => $this->progress->ranking(20),
        ]);
    }
}
