<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Request;
use App\Models\Municipality;
use App\Models\Quiz;
use RuntimeException;

final class QuizController extends Controller
{
    public function __construct(
        private readonly Quiz $quizzes = new Quiz(),
        private readonly Municipality $municipalities = new Municipality()
    ) {
    }

    public function index(Request $request): void
    {
        $this->view('admin/quizzes/index', [
            'title' => 'Quizzes',
            'user' => Auth::user(),
            'quizzes' => $this->quizzes->adminList(),
        ], 'layouts/admin');
    }

    public function create(Request $request): void
    {
        $this->view('admin/quizzes/form', [
            'title' => 'Novo quiz',
            'user' => Auth::user(),
            'quiz' => null,
            'municipalities' => $this->municipalities->adminList(),
            'formAction' => url('admin/quizzes'),
        ], 'layouts/admin');
    }

    public function store(Request $request): void
    {
        $user = Auth::user();
        $this->quizzes->create($this->validate($request, $user['id'] ?? null));

        $this->redirectWithMessage('admin/quizzes', 'success', 'Quiz cadastrado com sucesso.');
    }

    public function edit(Request $request): void
    {
        $quiz = $this->quizzes->find((int) $request->route('id'));
        if ($quiz === null) {
            $this->redirectWithMessage('admin/quizzes', 'error', 'Quiz nao encontrado.');
        }

        $this->view('admin/quizzes/form', [
            'title' => 'Editar quiz',
            'user' => Auth::user(),
            'quiz' => $quiz,
            'municipalities' => $this->municipalities->adminList(),
            'formAction' => url('admin/quizzes/' . $quiz['id_quiz']),
        ], 'layouts/admin');
    }

    public function update(Request $request): void
    {
        $quiz = $this->quizzes->find((int) $request->route('id'));
        if ($quiz === null) {
            $this->redirectWithMessage('admin/quizzes', 'error', 'Quiz nao encontrado.');
        }

        $user = Auth::user();
        $this->quizzes->update((int) $quiz['id_quiz'], $this->validate($request, $user['id'] ?? null, $quiz));

        $this->redirectWithMessage('admin/quizzes', 'success', 'Quiz atualizado com sucesso.');
    }

    private function validate(Request $request, ?int $userId, ?array $current = null): array
    {
        try {
            Csrf::validate((string) $request->input('_token'));
        } catch (RuntimeException $exception) {
            $this->redirectWithMessage('admin/quizzes', 'error', 'Token de seguranca invalido.');
        }

        $role = Auth::user()['role_slug'] ?? null;
        $status = trim((string) $request->input('status_curadoria', 'rascunho'));

        if ($role === 'editor' && !in_array($status, ['rascunho', 'enviado_revisao', 'em_correcao'], true)) {
            $status = $current['status_curadoria'] ?? 'rascunho';
        }

        return [
            'id_municipio' => (int) $request->input('id_municipio'),
            'titulo' => trim((string) $request->input('titulo')),
            'descricao' => trim((string) $request->input('descricao')),
            'status_curadoria' => $status,
            'criado_por' => $current['criado_por'] ?? $userId,
            'revisado_por' => in_array($status, ['aprovado', 'publicado', 'reprovado'], true) ? $userId : ($current['revisado_por'] ?? null),
            'publicado_por' => $status === 'publicado' ? $userId : ($current['publicado_por'] ?? null),
            'publicado_em' => $status === 'publicado' ? ($current['publicado_em'] ?? date('Y-m-d H:i:s')) : null,
        ];
    }
}
