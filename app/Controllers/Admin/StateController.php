<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Session;
use App\Models\State;
use App\Services\IbgeLocalidadesService;
use RuntimeException;
use Throwable;

final class StateController extends Controller
{
    public function __construct(
        private readonly State $states = new State(),
        private readonly IbgeLocalidadesService $ibge = new IbgeLocalidadesService()
    ) {
    }

    public function index(Request $request): void
    {
        $this->view('admin/states/index', [
            'title' => 'Estados',
            'user' => Auth::user(),
            'states' => $this->states->all(),
            'message' => Session::getFlash('message'),
        ], 'layouts/admin');
    }

    public function create(Request $request): void
    {
        $this->view('admin/states/form', [
            'title' => 'Novo estado',
            'user' => Auth::user(),
            'state' => null,
            'formAction' => url('admin/estados'),
        ], 'layouts/admin');
    }

    public function store(Request $request): void
    {
        $data = $this->validate($request);
        $this->states->create($data);

        $this->redirectWithMessage('admin/estados', 'success', 'Estado cadastrado com sucesso.');
    }

    public function edit(Request $request): void
    {
        $state = $this->states->find((int) $request->route('id'));

        if ($state === null) {
            $this->redirectWithMessage('admin/estados', 'error', 'Estado nao encontrado.');
        }

        $this->view('admin/states/form', [
            'title' => 'Editar estado',
            'user' => Auth::user(),
            'state' => $state,
            'formAction' => url('admin/estados/' . $state['id_estado']),
        ], 'layouts/admin');
    }

    public function update(Request $request): void
    {
        $state = $this->states->find((int) $request->route('id'));

        if ($state === null) {
            $this->redirectWithMessage('admin/estados', 'error', 'Estado nao encontrado.');
        }

        $data = $this->validate($request);
        $this->states->update((int) $state['id_estado'], $data);

        $this->redirectWithMessage('admin/estados', 'success', 'Estado atualizado com sucesso.');
    }

    public function import(Request $request): void
    {
        try {
            Csrf::validate((string) $request->input('_token'));
            $count = $this->ibge->importStates(Auth::user()['id'] ?? null);
            $this->redirectWithMessage('admin/estados', 'success', "{$count} estados importados ou atualizados via IBGE.");
        } catch (Throwable $exception) {
            $this->redirectWithMessage('admin/estados', 'error', $exception->getMessage());
        }
    }

    private function validate(Request $request): array
    {
        try {
            Csrf::validate((string) $request->input('_token'));
        } catch (RuntimeException $exception) {
            $this->redirectWithMessage('admin/estados', 'error', 'Token de seguranca invalido.');
        }

        $codigo = trim((string) $request->input('codigo_ibge'));
        $nome = trim((string) $request->input('nome'));
        $sigla = trim((string) $request->input('sigla'));
        $regiao = trim((string) $request->input('regiao'));
        $slug = trim((string) $request->input('slug'));
        $status = trim((string) $request->input('status', 'ativo'));

        if ($codigo === '' || $nome === '' || $sigla === '' || $regiao === '') {
            $this->redirectWithMessage('admin/estados', 'warning', 'Preencha codigo IBGE, nome, sigla e regiao.');
        }

        return [
            'codigo_ibge' => (int) $codigo,
            'nome' => $nome,
            'sigla' => strtoupper($sigla),
            'regiao' => $regiao,
            'slug' => $slug !== '' ? slugify($slug) : slugify($nome),
            'status' => in_array($status, ['rascunho', 'ativo', 'inativo'], true) ? $status : 'ativo',
        ];
    }
}
