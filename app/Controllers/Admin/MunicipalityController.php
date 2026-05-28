<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Request;
use App\Models\CurationHistory;
use App\Models\Municipality;
use App\Models\Qrcode;
use App\Models\State;
use App\Services\IbgeLocalidadesService;
use App\Services\IbgeCityFactsService;
use App\Services\MunicipalityEditorialScaffolder;
use Throwable;
use RuntimeException;

final class MunicipalityController extends Controller
{
    public function __construct(
        private readonly Municipality $municipalities = new Municipality(),
        private readonly State $states = new State(),
        private readonly IbgeLocalidadesService $ibge = new IbgeLocalidadesService(),
        private readonly IbgeCityFactsService $cityFacts = new IbgeCityFactsService(),
        private readonly MunicipalityEditorialScaffolder $scaffolder = new MunicipalityEditorialScaffolder(),
        private readonly CurationHistory $history = new CurationHistory(),
        private readonly Qrcode $qrcodes = new Qrcode()
    ) {
    }

    public function index(Request $request): void
    {
        $stateId = $request->input('estado_id');
        $search = trim((string) $request->input('busca', ''));
        $coordinatesFilter = trim((string) $request->input('coordenadas', ''));
        $publicationFilter = trim((string) $request->input('publicacao', ''));
        $states = $this->states->activeForSelect();
        $selectedState = null;
        if ($stateId !== null && $stateId !== '') {
            foreach ($states as $state) {
                if ((int) $state['id_estado'] === (int) $stateId) {
                    $selectedState = $state;
                    break;
                }
            }
        }
        $selectedStateSlug = $selectedState['slug'] ?? null;
        $municipalities = $this->municipalities->adminList(
            $stateId !== null && $stateId !== '' ? (int) $stateId : null,
            $search,
            $coordinatesFilter,
            $publicationFilter
        );
        $municipalities = $this->municipalities->enrichWithEditorialReadiness($municipalities);
        $municipalities = $this->municipalities->enrichWithFactualCoverage($municipalities);
        $publishedMunicipalities = array_values(array_filter(
            $municipalities,
            static fn (array $municipality): bool => $municipality['status_publicacao'] === 'publicado'
        ));
        $editorialSummary = [
            'autoral' => count(array_filter($municipalities, static fn (array $municipality): bool => ($municipality['editorial_status'] ?? '') === 'autoral')),
            'em_curadoria' => count(array_filter($municipalities, static fn (array $municipality): bool => ($municipality['editorial_status'] ?? '') === 'em_curadoria')),
            'base' => count(array_filter($municipalities, static fn (array $municipality): bool => ($municipality['editorial_status'] ?? '') === 'base')),
        ];
        $factualSummary = [
            'completo' => count(array_filter($municipalities, static fn (array $municipality): bool => ($municipality['facts_status'] ?? '') === 'completo')),
            'parcial' => count(array_filter($municipalities, static fn (array $municipality): bool => ($municipality['facts_status'] ?? '') === 'parcial')),
            'vazio' => count(array_filter($municipalities, static fn (array $municipality): bool => ($municipality['facts_status'] ?? '') === 'vazio')),
        ];
        $officialMunicipalities = $this->municipalities->officialCount($selectedStateSlug);
        $missingGeoMunicipalities = $this->municipalities->publicMissingFromGeoJson($selectedStateSlug);

        $this->view('admin/municipalities/index', [
            'title' => 'Municipios',
            'user' => Auth::user(),
            'states' => $states,
            'selectedStateId' => $stateId !== null && $stateId !== '' ? (int) $stateId : null,
            'search' => $search,
            'selectedCoordinatesFilter' => $coordinatesFilter,
            'selectedPublicationFilter' => $publicationFilter,
            'municipalities' => $municipalities,
            'summary' => [
                'total' => count($municipalities),
                'official' => $officialMunicipalities,
                'withCoordinates' => count(array_filter($municipalities, static fn (array $municipality): bool => $municipality['latitude'] !== null && $municipality['longitude'] !== null)),
                'withoutCoordinates' => count(array_filter($municipalities, static fn (array $municipality): bool => $municipality['latitude'] === null || $municipality['longitude'] === null)),
                'published' => count($publishedMunicipalities),
                'missingPublication' => max(0, $officialMunicipalities - count($publishedMunicipalities)),
                'missingGeo' => count($missingGeoMunicipalities),
            ],
            'editorialSummary' => $editorialSummary,
            'factualSummary' => $factualSummary,
            'missingGeoMunicipalities' => $missingGeoMunicipalities,
            'priorityMunicipalities' => $this->municipalities->editorialPriorityList(),
        ], 'layouts/admin');
    }

    public function create(Request $request): void
    {
        $this->view('admin/municipalities/form', [
            'title' => 'Novo municipio',
            'user' => Auth::user(),
            'municipality' => null,
            'states' => $this->states->activeForSelect(),
            'formAction' => url('admin/municipios'),
        ], 'layouts/admin');
    }

    public function store(Request $request): void
    {
        $user = Auth::user();
        $data = $this->validate($request, $user['id'] ?? null);
        $municipalityId = $this->municipalities->create($data);
        $this->history->create([
            'entidade_tipo' => 'municipio',
            'entidade_id' => $municipalityId,
            'acao' => 'criacao',
            'status_anterior' => null,
            'status_novo' => $data['status_curadoria'],
            'observacao' => 'Municipio criado no painel administrativo.',
            'usuario_id' => $user['id'] ?? null,
        ]);

        $this->redirectWithMessage('admin/municipios', 'success', 'Municipio cadastrado com sucesso.');
    }

    public function edit(Request $request): void
    {
        $municipality = $this->municipalities->find((int) $request->route('id'));

        if ($municipality === null) {
            $this->redirectWithMessage('admin/municipios', 'error', 'Municipio nao encontrado.');
        }
        $municipality = $this->municipalities->editorialCoverage($municipality);
        $municipality = $this->municipalities->factualCoverage($municipality);

        $this->view('admin/municipalities/form', [
            'title' => 'Editar municipio',
            'user' => Auth::user(),
            'municipality' => $municipality,
            'states' => $this->states->activeForSelect(),
            'formAction' => url('admin/municipios/' . $municipality['id_municipio']),
            'history' => $this->history->byEntity('municipio', (int) $municipality['id_municipio']),
        ], 'layouts/admin');
    }

    public function update(Request $request): void
    {
        $municipality = $this->municipalities->find((int) $request->route('id'));

        if ($municipality === null) {
            $this->redirectWithMessage('admin/municipios', 'error', 'Municipio nao encontrado.');
        }

        $user = Auth::user();
        $data = $this->validate($request, $user['id'] ?? null, $municipality);
        $this->municipalities->update((int) $municipality['id_municipio'], $data);
        $this->history->create([
            'entidade_tipo' => 'municipio',
            'entidade_id' => (int) $municipality['id_municipio'],
            'acao' => 'edicao',
            'status_anterior' => $municipality['status_curadoria'],
            'status_novo' => $data['status_curadoria'],
            'observacao' => 'Conteudo editado no formulario administrativo.',
            'usuario_id' => $user['id'] ?? null,
        ]);

        $this->redirectWithMessage('admin/municipios', 'success', 'Municipio atualizado com sucesso.');
    }

    public function import(Request $request): void
    {
        try {
            Csrf::validate((string) $request->input('_token'));

            $stateIdentifier = trim((string) $request->input('estado_referencia'));
            if ($stateIdentifier === '') {
                $this->redirectWithMessage('admin/municipios', 'warning', 'Informe a sigla ou codigo IBGE do estado para importar.');
            }

            $count = $this->ibge->importMunicipalities($stateIdentifier, Auth::user()['id'] ?? null);
            $this->redirectWithMessage('admin/municipios', 'success', "{$count} municipios importados ou atualizados via IBGE.");
        } catch (Throwable $exception) {
            $this->redirectWithMessage('admin/municipios', 'error', $exception->getMessage());
        }
    }

    public function importFacts(Request $request): void
    {
        try {
            Csrf::validate((string) $request->input('_token'));

            $stateIdentifier = trim((string) $request->input('estado_dados'));
            if ($stateIdentifier === '') {
                $this->redirectWithMessage('admin/municipios', 'warning', 'Informe a sigla ou codigo IBGE do estado para importar dados factuais.');
            }

            $count = $this->cityFacts->importStateFacts($stateIdentifier, Auth::user()['id'] ?? null, [
                'only_missing' => false,
                'continue_on_error' => true,
                'delay_ms' => 120,
            ]);
            $this->redirectWithMessage('admin/municipios', 'success', "{$count} municipios tiveram populacao, area, densidade ou gentilico enriquecidos via IBGE.");
        } catch (Throwable $exception) {
            $this->redirectWithMessage('admin/municipios', 'error', $exception->getMessage());
        }
    }

    public function bulkPublish(Request $request): void
    {
        try {
            Csrf::validate((string) $request->input('_token'));

            $stateIdentifier = trim((string) $request->input('estado_publicacao'));
            if ($stateIdentifier === '') {
                $this->redirectWithMessage('admin/municipios', 'warning', 'Informe a sigla ou codigo IBGE do estado para publicar em massa.');
            }

            $state = $this->states->findByCodeOrSigla($stateIdentifier);
            if ($state === null) {
                $this->redirectWithMessage('admin/municipios', 'error', 'Estado nao encontrado para publicacao em massa.');
            }

            $count = $this->municipalities->bulkPublishByState((int) $state['id_estado'], Auth::user()['id'] ?? null);
            $this->redirectWithMessage('admin/municipios', 'success', "{$count} municipios ajustados para publicacao inicial em massa.");
        } catch (Throwable $exception) {
            $this->redirectWithMessage('admin/municipios', 'error', $exception->getMessage());
        }
    }

    public function generateEditorialBase(Request $request): void
    {
        try {
            Csrf::validate((string) $request->input('_token'));
        } catch (RuntimeException $exception) {
            $this->redirectWithMessage('admin/municipios', 'error', 'Token de seguranca invalido.');
        }

        $municipality = $this->municipalities->find((int) $request->route('id'));
        if ($municipality === null) {
            $this->redirectWithMessage('admin/municipios', 'error', 'Municipio nao encontrado.');
        }

        $payload = $this->scaffolder->buildPayload($municipality);
        $this->municipalities->update((int) $municipality['id_municipio'], $payload);

        $user = Auth::user();
        $this->history->create([
            'entidade_tipo' => 'municipio',
            'entidade_id' => (int) $municipality['id_municipio'],
            'acao' => 'gerar_base_editorial',
            'status_anterior' => $municipality['status_curadoria'],
            'status_novo' => $payload['status_curadoria'],
            'observacao' => 'Base editorial inicial gerada automaticamente para secoes vazias.',
            'usuario_id' => $user['id'] ?? null,
        ]);

        $this->redirectWithMessage(
            'admin/municipios/' . $municipality['id_municipio'] . '/editar',
            'success',
            'Base editorial inicial aplicada nas secoes que ainda estavam vazias.'
        );
    }

    public function bulkGenerateEditorialBase(Request $request): void
    {
        try {
            Csrf::validate((string) $request->input('_token'));

            $stateIdentifier = trim((string) $request->input('estado_editorial'));
            if ($stateIdentifier === '') {
                $this->redirectWithMessage('admin/municipios', 'warning', 'Informe a sigla ou codigo IBGE do estado para gerar a base editorial em massa.');
            }

            $state = $this->states->findByCodeOrSigla($stateIdentifier);
            if ($state === null) {
                $this->redirectWithMessage('admin/municipios', 'error', 'Estado nao encontrado para geracao editorial em massa.');
            }

            $municipalities = $this->municipalities->adminList((int) $state['id_estado']);
            $user = Auth::user();
            $count = 0;

            foreach ($municipalities as $municipality) {
                $payload = $this->scaffolder->buildPayload($municipality, $user['id'] ?? null);
                $hasChanges = false;
                foreach (['descricao_curta', 'historia', 'geografia', 'economia', 'cultura', 'turismo', 'educacao', 'curiosidades'] as $field) {
                    if (($payload[$field] ?? '') !== (string) ($municipality[$field] ?? '')) {
                        $hasChanges = true;
                        break;
                    }
                }

                if (!$hasChanges) {
                    continue;
                }

                $this->municipalities->update((int) $municipality['id_municipio'], $payload);
                $this->history->create([
                    'entidade_tipo' => 'municipio',
                    'entidade_id' => (int) $municipality['id_municipio'],
                    'acao' => 'gerar_base_editorial_em_massa',
                    'status_anterior' => $municipality['status_curadoria'],
                    'status_novo' => $payload['status_curadoria'],
                    'observacao' => 'Base editorial inicial gerada em massa para secoes vazias.',
                    'usuario_id' => $user['id'] ?? null,
                ]);
                $count++;
            }

            $this->redirectWithMessage(
                'admin/municipios',
                'success',
                "{$count} municipio(s) receberam base editorial inicial no estado selecionado."
            );
        } catch (Throwable $exception) {
            $this->redirectWithMessage('admin/municipios', 'error', $exception->getMessage());
        }
    }

    public function submitForReview(Request $request): void
    {
        $this->changeWorkflowStatus($request, 'enviado_revisao', 'enviar_revisao', 'Municipio enviado para revisao.');
    }

    public function requestCorrection(Request $request): void
    {
        $this->assertRole(['curador', 'administrador-geral']);
        $this->changeWorkflowStatus($request, 'em_correcao', 'solicitar_correcao', 'Correcao solicitada ao editor.');
    }

    public function approve(Request $request): void
    {
        $this->assertRole(['curador', 'administrador-geral']);
        $this->changeWorkflowStatus($request, 'aprovado', 'aprovar', 'Municipio aprovado pela curadoria.');
    }

    public function reject(Request $request): void
    {
        $this->assertRole(['curador', 'administrador-geral']);
        $this->changeWorkflowStatus($request, 'reprovado', 'reprovar', 'Municipio reprovado na curadoria.');
    }

    public function publish(Request $request): void
    {
        $this->assertRole(['administrador-geral']);
        $this->changeWorkflowStatus($request, 'publicado', 'publicar', 'Municipio publicado com sucesso.', true);
    }

    public function archive(Request $request): void
    {
        $this->assertRole(['administrador-geral']);
        $this->changeWorkflowStatus($request, 'arquivado', 'arquivar', 'Municipio arquivado.');
    }

    private function validate(Request $request, ?int $userId, ?array $current = null): array
    {
        try {
            Csrf::validate((string) $request->input('_token'));
        } catch (RuntimeException $exception) {
            $this->redirectWithMessage('admin/municipios', 'error', 'Token de seguranca invalido.');
        }

        $nome = trim((string) $request->input('nome'));
        $codigo = trim((string) $request->input('codigo_ibge'));
        $estadoId = trim((string) $request->input('id_estado'));

        if ($nome === '' || $codigo === '' || $estadoId === '') {
            $this->redirectWithMessage('admin/municipios', 'warning', 'Nome, codigo IBGE e estado sao obrigatorios.');
        }

        $statusPublicacao = trim((string) $request->input('status_publicacao', 'rascunho'));
        $statusCuradoria = trim((string) $request->input('status_curadoria', 'rascunho'));
        $role = Auth::user()['role_slug'] ?? null;

        if ($role === 'editor') {
            $statusPublicacao = $current['status_publicacao'] ?? 'rascunho';

            if (!in_array($statusCuradoria, ['rascunho', 'em_correcao', 'enviado_revisao'], true)) {
                $statusCuradoria = $current['status_curadoria'] ?? 'rascunho';
            }
        }

        if ($role === 'curador' && $statusPublicacao === 'publicado') {
            $statusPublicacao = $current['status_publicacao'] ?? 'rascunho';
        }

        return [
            'id_estado' => (int) $estadoId,
            'codigo_ibge' => (int) $codigo,
            'nome' => $nome,
            'slug' => trim((string) $request->input('slug')) !== '' ? slugify((string) $request->input('slug')) : slugify($nome),
            'descricao_curta' => trim((string) $request->input('descricao_curta')),
            'historia' => trim((string) $request->input('historia')),
            'geografia' => trim((string) $request->input('geografia')),
            'economia' => trim((string) $request->input('economia')),
            'cultura' => trim((string) $request->input('cultura')),
            'turismo' => trim((string) $request->input('turismo')),
            'educacao' => trim((string) $request->input('educacao')),
            'curiosidades' => trim((string) $request->input('curiosidades')),
            'populacao' => trim((string) $request->input('populacao')),
            'area' => trim((string) $request->input('area')),
            'densidade_demografica' => trim((string) $request->input('densidade_demografica')),
            'gentilico' => trim((string) $request->input('gentilico')),
            'data_fundacao' => trim((string) $request->input('data_fundacao')),
            'latitude' => trim((string) $request->input('latitude')),
            'longitude' => trim((string) $request->input('longitude')),
            'distancia_capital' => trim((string) $request->input('distancia_capital')),
            'imagem_principal' => trim((string) $request->input('imagem_principal')),
            'fonte_dados' => trim((string) $request->input('fonte_dados')),
            'status_curadoria' => $statusCuradoria,
            'status_publicacao' => $statusPublicacao,
            'criado_por' => $current['criado_por'] ?? $userId,
            'atualizado_por' => $userId,
            'revisado_por' => ($role === 'curador' || $role === 'administrador-geral') && in_array($statusCuradoria, ['aprovado', 'reprovado', 'em_correcao', 'publicado'], true) ? $userId : ($current['revisado_por'] ?? null),
            'publicado_por' => $statusPublicacao === 'publicado' ? $userId : null,
            'publicado_em' => $statusPublicacao === 'publicado' ? ($current['publicado_em'] ?? date('Y-m-d H:i:s')) : null,
        ];
    }

    private function changeWorkflowStatus(Request $request, string $newStatus, string $action, string $message, bool $publish = false): void
    {
        try {
            Csrf::validate((string) $request->input('_token'));
        } catch (RuntimeException $exception) {
            $this->redirectWithMessage('admin/municipios', 'error', 'Token de seguranca invalido.');
        }

        $municipality = $this->municipalities->find((int) $request->route('id'));
        if ($municipality === null) {
            $this->redirectWithMessage('admin/municipios', 'error', 'Municipio nao encontrado.');
        }

        $user = Auth::user();
        $statusAnterior = $municipality['status_curadoria'];
        $observation = trim((string) $request->input('observacao_curadoria', ''));

        $payload = [
            'id_estado' => (int) $municipality['id_estado'],
            'codigo_ibge' => (int) $municipality['codigo_ibge'],
            'nome' => $municipality['nome'],
            'slug' => $municipality['slug'],
            'descricao_curta' => $municipality['descricao_curta'] ?? '',
            'historia' => $municipality['historia'] ?? '',
            'geografia' => $municipality['geografia'] ?? '',
            'economia' => $municipality['economia'] ?? '',
            'cultura' => $municipality['cultura'] ?? '',
            'turismo' => $municipality['turismo'] ?? '',
            'educacao' => $municipality['educacao'] ?? '',
            'curiosidades' => $municipality['curiosidades'] ?? '',
            'populacao' => (string) ($municipality['populacao'] ?? ''),
            'area' => (string) ($municipality['area'] ?? ''),
            'densidade_demografica' => (string) ($municipality['densidade_demografica'] ?? ''),
            'gentilico' => $municipality['gentilico'] ?? '',
            'data_fundacao' => $municipality['data_fundacao'] ?? '',
            'latitude' => (string) ($municipality['latitude'] ?? ''),
            'longitude' => (string) ($municipality['longitude'] ?? ''),
            'distancia_capital' => (string) ($municipality['distancia_capital'] ?? ''),
            'imagem_principal' => $municipality['imagem_principal'] ?? '',
            'fonte_dados' => $municipality['fonte_dados'] ?? '',
            'status_curadoria' => $newStatus,
            'status_publicacao' => $publish ? 'publicado' : (($newStatus === 'arquivado' || $newStatus === 'reprovado') ? 'despublicado' : $municipality['status_publicacao']),
            'criado_por' => $municipality['criado_por'] ?? null,
            'atualizado_por' => $user['id'] ?? null,
            'revisado_por' => in_array($newStatus, ['aprovado', 'reprovado', 'em_correcao', 'publicado'], true) ? ($user['id'] ?? null) : ($municipality['revisado_por'] ?? null),
            'publicado_por' => $publish ? ($user['id'] ?? null) : (($newStatus === 'arquivado' || $newStatus === 'reprovado') ? null : ($municipality['publicado_por'] ?? null)),
            'publicado_em' => $publish ? date('Y-m-d H:i:s') : (($newStatus === 'arquivado' || $newStatus === 'reprovado') ? null : ($municipality['publicado_em'] ?? null)),
        ];

        $this->municipalities->update((int) $municipality['id_municipio'], $payload);
        if ($publish) {
            $updatedMunicipality = $this->municipalities->find((int) $municipality['id_municipio']);
            if ($updatedMunicipality !== null) {
                $this->qrcodes->ensureForMunicipality($updatedMunicipality);
            }
        }
        $this->history->create([
            'entidade_tipo' => 'municipio',
            'entidade_id' => (int) $municipality['id_municipio'],
            'acao' => $action,
            'status_anterior' => $statusAnterior,
            'status_novo' => $newStatus,
            'observacao' => $observation !== '' ? $observation : null,
            'usuario_id' => $user['id'] ?? null,
        ]);

        $this->redirectWithMessage('admin/municipios/' . $municipality['id_municipio'] . '/editar', 'success', $message);
    }

    private function assertRole(array $roles): void
    {
        if (!Auth::hasRole($roles)) {
            $this->redirectWithMessage('admin/municipios', 'error', 'Voce nao tem permissao para esta acao.');
        }
    }
}
