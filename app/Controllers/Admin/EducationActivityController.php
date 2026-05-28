<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Request;
use App\Models\EducationActivity;
use App\Models\Municipality;
use RuntimeException;

final class EducationActivityController extends Controller
{
    public function __construct(
        private readonly EducationActivity $activities = new EducationActivity(),
        private readonly Municipality $municipalities = new Municipality()
    ) {
    }

    public function index(Request $request): void
    {
        $this->view('admin/education/index', [
            'title' => 'Atividades pedagogicas',
            'user' => Auth::user(),
            'activities' => $this->activities->adminList(),
        ], 'layouts/admin');
    }

    public function create(Request $request): void
    {
        $this->view('admin/education/form', [
            'title' => 'Nova atividade pedagogica',
            'user' => Auth::user(),
            'activity' => null,
            'municipalities' => $this->municipalities->adminList(),
            'formAction' => url('admin/educacao'),
        ], 'layouts/admin');
    }

    public function store(Request $request): void
    {
        $user = Auth::user();
        $this->activities->create($this->validate($request, $user['id'] ?? null));

        $this->redirectWithMessage('admin/educacao', 'success', 'Atividade pedagogica cadastrada com sucesso.');
    }

    public function edit(Request $request): void
    {
        $activity = $this->activities->find((int) $request->route('id'));
        if ($activity === null) {
            $this->redirectWithMessage('admin/educacao', 'error', 'Atividade pedagogica nao encontrada.');
        }

        $this->view('admin/education/form', [
            'title' => 'Editar atividade pedagogica',
            'user' => Auth::user(),
            'activity' => $activity,
            'municipalities' => $this->municipalities->adminList(),
            'formAction' => url('admin/educacao/' . $activity['id_atividade']),
        ], 'layouts/admin');
    }

    public function update(Request $request): void
    {
        $activity = $this->activities->find((int) $request->route('id'));
        if ($activity === null) {
            $this->redirectWithMessage('admin/educacao', 'error', 'Atividade pedagogica nao encontrada.');
        }

        $user = Auth::user();
        $this->activities->update((int) $activity['id_atividade'], $this->validate($request, $user['id'] ?? null, $activity));

        $this->redirectWithMessage('admin/educacao', 'success', 'Atividade pedagogica atualizada com sucesso.');
    }

    private function validate(Request $request, ?int $userId, ?array $current = null): array
    {
        try {
            Csrf::validate((string) $request->input('_token'));
        } catch (RuntimeException $exception) {
            $this->redirectWithMessage('admin/educacao', 'error', 'Token de seguranca invalido.');
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
            'disciplina' => trim((string) $request->input('disciplina')),
            'ano_escolar' => trim((string) $request->input('ano_escolar')),
            'objetivo' => trim((string) $request->input('objetivo')),
            'metodologia' => trim((string) $request->input('metodologia')),
            'recursos_necessarios' => trim((string) $request->input('recursos_necessarios')),
            'desenvolvimento' => trim((string) $request->input('desenvolvimento')),
            'avaliacao' => trim((string) $request->input('avaliacao')),
            'arquivo_pdf' => trim((string) $request->input('arquivo_pdf')),
            'status_curadoria' => $status,
            'criado_por' => $current['criado_por'] ?? $userId,
            'revisado_por' => in_array($status, ['aprovado', 'publicado', 'reprovado'], true) ? $userId : ($current['revisado_por'] ?? null),
            'publicado_por' => $status === 'publicado' ? $userId : ($current['publicado_por'] ?? null),
        ];
    }
}
