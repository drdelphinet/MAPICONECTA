<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Request;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use RuntimeException;

final class QuizQuestionController extends Controller
{
    public function __construct(
        private readonly Quiz $quizzes = new Quiz(),
        private readonly QuizQuestion $questions = new QuizQuestion()
    ) {
    }

    public function index(Request $request): void
    {
        $quiz = $this->quizzes->find((int) $request->route('quiz_id'));
        if ($quiz === null) {
            $this->redirectWithMessage('admin/quizzes', 'error', 'Quiz nao encontrado.');
        }

        $this->view('admin/quizzes/questions/index', [
            'title' => 'Perguntas do quiz',
            'user' => Auth::user(),
            'quiz' => $quiz,
            'questions' => $this->questions->forQuizAdmin((int) $quiz['id_quiz']),
        ], 'layouts/admin');
    }

    public function create(Request $request): void
    {
        $quiz = $this->quizzes->find((int) $request->route('quiz_id'));
        if ($quiz === null) {
            $this->redirectWithMessage('admin/quizzes', 'error', 'Quiz nao encontrado.');
        }

        $this->view('admin/quizzes/questions/form', [
            'title' => 'Nova pergunta',
            'user' => Auth::user(),
            'quiz' => $quiz,
            'question' => null,
            'formAction' => url('admin/quizzes/' . $quiz['id_quiz'] . '/perguntas'),
        ], 'layouts/admin');
    }

    public function store(Request $request): void
    {
        $quiz = $this->quizzes->find((int) $request->route('quiz_id'));
        if ($quiz === null) {
            $this->redirectWithMessage('admin/quizzes', 'error', 'Quiz nao encontrado.');
        }

        $this->questions->create($this->validate($request, (int) $quiz['id_quiz']));
        $this->redirectWithMessage('admin/quizzes/' . $quiz['id_quiz'] . '/perguntas', 'success', 'Pergunta cadastrada com sucesso.');
    }

    public function edit(Request $request): void
    {
        $quiz = $this->quizzes->find((int) $request->route('quiz_id'));
        $question = $this->questions->find((int) $request->route('id'));

        if ($quiz === null || $question === null) {
            $this->redirectWithMessage('admin/quizzes', 'error', 'Pergunta ou quiz nao encontrado.');
        }

        $this->view('admin/quizzes/questions/form', [
            'title' => 'Editar pergunta',
            'user' => Auth::user(),
            'quiz' => $quiz,
            'question' => $question,
            'formAction' => url('admin/quizzes/' . $quiz['id_quiz'] . '/perguntas/' . $question['id_pergunta']),
        ], 'layouts/admin');
    }

    public function update(Request $request): void
    {
        $quiz = $this->quizzes->find((int) $request->route('quiz_id'));
        $question = $this->questions->find((int) $request->route('id'));

        if ($quiz === null || $question === null) {
            $this->redirectWithMessage('admin/quizzes', 'error', 'Pergunta ou quiz nao encontrado.');
        }

        $this->questions->update((int) $question['id_pergunta'], $this->validate($request, (int) $quiz['id_quiz'], $question));
        $this->redirectWithMessage('admin/quizzes/' . $quiz['id_quiz'] . '/perguntas', 'success', 'Pergunta atualizada com sucesso.');
    }

    private function validate(Request $request, int $quizId, ?array $current = null): array
    {
        try {
            Csrf::validate((string) $request->input('_token'));
        } catch (RuntimeException $exception) {
            $this->redirectWithMessage('admin/quizzes/' . $quizId . '/perguntas', 'error', 'Token de seguranca invalido.');
        }

        $role = Auth::user()['role_slug'] ?? null;
        $status = trim((string) $request->input('status_curadoria', 'rascunho'));
        if ($role === 'editor' && !in_array($status, ['rascunho', 'enviado_revisao', 'em_correcao'], true)) {
            $status = $current['status_curadoria'] ?? 'rascunho';
        }

        return [
            'id_quiz' => $quizId,
            'pergunta' => trim((string) $request->input('pergunta')),
            'alternativa_a' => trim((string) $request->input('alternativa_a')),
            'alternativa_b' => trim((string) $request->input('alternativa_b')),
            'alternativa_c' => trim((string) $request->input('alternativa_c')),
            'alternativa_d' => trim((string) $request->input('alternativa_d')),
            'resposta_correta' => trim((string) $request->input('resposta_correta')),
            'explicacao' => trim((string) $request->input('explicacao')),
            'pontos' => max(1, (int) $request->input('pontos', 10)),
            'ordem' => max(1, (int) $request->input('ordem', 1)),
            'status_curadoria' => $status,
            'criado_por' => $current['criado_por'] ?? (Auth::user()['id'] ?? null),
            'revisado_por' => in_array($status, ['aprovado', 'publicado', 'reprovado'], true) ? (Auth::user()['id'] ?? null) : ($current['revisado_por'] ?? null),
            'publicado_por' => $status === 'publicado' ? (Auth::user()['id'] ?? null) : ($current['publicado_por'] ?? null),
        ];
    }
}
