<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Request;
use App\Models\Municipality;
use App\Models\PointOfInterest;
use RuntimeException;

final class PointOfInterestController extends Controller
{
    public function __construct(
        private readonly PointOfInterest $points = new PointOfInterest(),
        private readonly Municipality $municipalities = new Municipality()
    ) {
    }

    public function index(Request $request): void
    {
        $this->view('admin/tourism/index', [
            'title' => 'Pontos turisticos',
            'user' => Auth::user(),
            'points' => $this->points->adminList(),
        ], 'layouts/admin');
    }

    public function create(Request $request): void
    {
        $this->view('admin/tourism/form', [
            'title' => 'Novo ponto turistico',
            'user' => Auth::user(),
            'point' => null,
            'municipalities' => $this->municipalities->adminList(),
            'formAction' => url('admin/turismo'),
        ], 'layouts/admin');
    }

    public function store(Request $request): void
    {
        $user = Auth::user();
        $this->points->create($this->validate($request, $user['id'] ?? null));

        $this->redirectWithMessage('admin/turismo', 'success', 'Ponto turistico cadastrado com sucesso.');
    }

    public function edit(Request $request): void
    {
        $point = $this->points->find((int) $request->route('id'));
        if ($point === null) {
            $this->redirectWithMessage('admin/turismo', 'error', 'Ponto turistico nao encontrado.');
        }

        $this->view('admin/tourism/form', [
            'title' => 'Editar ponto turistico',
            'user' => Auth::user(),
            'point' => $point,
            'municipalities' => $this->municipalities->adminList(),
            'formAction' => url('admin/turismo/' . $point['id_ponto_turistico']),
        ], 'layouts/admin');
    }

    public function update(Request $request): void
    {
        $point = $this->points->find((int) $request->route('id'));
        if ($point === null) {
            $this->redirectWithMessage('admin/turismo', 'error', 'Ponto turistico nao encontrado.');
        }

        $user = Auth::user();
        $this->points->update((int) $point['id_ponto_turistico'], $this->validate($request, $user['id'] ?? null, $point));

        $this->redirectWithMessage('admin/turismo', 'success', 'Ponto turistico atualizado com sucesso.');
    }

    private function validate(Request $request, ?int $userId, ?array $current = null): array
    {
        try {
            Csrf::validate((string) $request->input('_token'));
        } catch (RuntimeException $exception) {
            $this->redirectWithMessage('admin/turismo', 'error', 'Token de seguranca invalido.');
        }

        $role = Auth::user()['role_slug'] ?? null;
        $status = trim((string) $request->input('status_curadoria', 'rascunho'));

        if ($role === 'editor' && !in_array($status, ['rascunho', 'enviado_revisao', 'em_correcao'], true)) {
            $status = $current['status_curadoria'] ?? 'rascunho';
        }

        return [
            'id_municipio' => (int) $request->input('id_municipio'),
            'nome' => trim((string) $request->input('nome')),
            'categoria' => trim((string) $request->input('categoria')),
            'descricao' => trim((string) $request->input('descricao')),
            'endereco' => trim((string) $request->input('endereco')),
            'latitude' => trim((string) $request->input('latitude')),
            'longitude' => trim((string) $request->input('longitude')),
            'horario_funcionamento' => trim((string) $request->input('horario_funcionamento')),
            'valor_entrada' => trim((string) $request->input('valor_entrada')),
            'contato' => trim((string) $request->input('contato')),
            'imagem' => trim((string) $request->input('imagem')),
            'fonte' => trim((string) $request->input('fonte')),
            'status_curadoria' => $status,
            'criado_por' => $current['criado_por'] ?? $userId,
            'revisado_por' => in_array($status, ['aprovado', 'publicado', 'reprovado'], true) ? $userId : ($current['revisado_por'] ?? null),
            'publicado_por' => $status === 'publicado' ? $userId : ($current['publicado_por'] ?? null),
        ];
    }
}
