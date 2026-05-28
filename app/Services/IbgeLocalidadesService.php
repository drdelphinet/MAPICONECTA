<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\IntegrationLog;
use App\Models\Municipality;
use App\Models\State;
use RuntimeException;

final class IbgeLocalidadesService
{
    private const BASE_URL = 'https://servicodados.ibge.gov.br/api/v1/localidades';
    private const KNOWN_COORDINATES_BY_SLUG = [
        'nazaria' => [
            'latitude' => '-5.3543729',
            'longitude' => '-42.8183515',
            'source' => 'Coordenada operacional validada manualmente para marcador do mapa',
        ],
    ];

    public function __construct(
        private readonly State $states = new State(),
        private readonly Municipality $municipalities = new Municipality(),
        private readonly IntegrationLog $logs = new IntegrationLog()
    ) {
    }

    public function importStates(?int $userId = null): int
    {
        $endpoint = self::BASE_URL . '/estados';

        try {
            $items = $this->fetchJson($endpoint);
            $count = 0;

            foreach ($items as $item) {
                $this->states->upsertFromIbge([
                    'codigo_ibge' => (int) $item['id'],
                    'nome' => $item['nome'],
                    'sigla' => $item['sigla'],
                    'regiao' => $item['regiao']['nome'] ?? 'Nao informada',
                    'slug' => slugify($item['nome']),
                    'status' => 'ativo',
                ]);
                $count++;
            }

            $this->logs->create([
                'fonte' => 'IBGE Localidades',
                'endpoint' => $endpoint,
                'parametros' => null,
                'status' => 'sucesso',
                'mensagem' => 'Estados importados com sucesso.',
                'resposta_resumida' => 'Total importado: ' . $count,
                'usuario_id' => $userId,
            ]);

            return $count;
        } catch (RuntimeException $exception) {
            $this->logs->create([
                'fonte' => 'IBGE Localidades',
                'endpoint' => $endpoint,
                'parametros' => null,
                'status' => 'erro',
                'mensagem' => $exception->getMessage(),
                'resposta_resumida' => null,
                'usuario_id' => $userId,
            ]);

            throw $exception;
        }
    }

    public function importMunicipalities(string $stateCodeOrSigla, ?int $userId = null): int
    {
        $state = $this->states->findByCodeOrSigla($stateCodeOrSigla);

        if ($state === null) {
            throw new RuntimeException('Estado nao encontrado para importacao.');
        }

        $endpoint = self::BASE_URL . '/estados/' . strtoupper($state['sigla']) . '/municipios';

        try {
            $items = $this->fetchJson($endpoint);
            $count = 0;

            foreach ($items as $item) {
                $slug = slugify($item['nome']);
                $knownCoordinates = self::KNOWN_COORDINATES_BY_SLUG[$slug] ?? null;

                $this->municipalities->upsertFromIbge([
                    'id_estado' => (int) $state['id_estado'],
                    'codigo_ibge' => (int) $item['id'],
                    'nome' => $item['nome'],
                    'slug' => $slug,
                    'descricao_curta' => null,
                    'historia' => null,
                    'geografia' => null,
                    'economia' => null,
                    'cultura' => null,
                    'turismo' => null,
                    'educacao' => null,
                    'curiosidades' => null,
                    'populacao' => null,
                    'area' => null,
                    'densidade_demografica' => null,
                    'gentilico' => null,
                    'data_fundacao' => null,
                    'latitude' => $knownCoordinates['latitude'] ?? null,
                    'longitude' => $knownCoordinates['longitude'] ?? null,
                    'distancia_capital' => null,
                    'imagem_principal' => null,
                    'fonte_dados' => $knownCoordinates !== null
                        ? 'IBGE Localidades; ' . $knownCoordinates['source']
                        : 'IBGE Localidades',
                    'status_curadoria' => 'rascunho',
                    'status_publicacao' => 'rascunho',
                    'criado_por' => $userId,
                    'atualizado_por' => $userId,
                    'publicado_por' => null,
                    'publicado_em' => null,
                ]);
                $count++;
            }

            $this->logs->create([
                'fonte' => 'IBGE Localidades',
                'endpoint' => $endpoint,
                'parametros' => ['uf' => strtoupper($state['sigla'])],
                'status' => 'sucesso',
                'mensagem' => 'Municipios importados com sucesso.',
                'resposta_resumida' => 'Total importado: ' . $count,
                'usuario_id' => $userId,
            ]);

            return $count;
        } catch (RuntimeException $exception) {
            $this->logs->create([
                'fonte' => 'IBGE Localidades',
                'endpoint' => $endpoint,
                'parametros' => ['uf' => strtoupper($state['sigla'])],
                'status' => 'erro',
                'mensagem' => $exception->getMessage(),
                'resposta_resumida' => null,
                'usuario_id' => $userId,
            ]);

            throw $exception;
        }
    }

    private function fetchJson(string $url): array
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 20,
                'header' => "Accept: application/json\r\nUser-Agent: MapiConecta/1.0\r\n",
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            throw new RuntimeException('Falha ao consultar a API de Localidades do IBGE.');
        }

        $decoded = json_decode($response, true);

        if (!is_array($decoded)) {
            throw new RuntimeException('Resposta invalida da API de Localidades do IBGE.');
        }

        return $decoded;
    }
}
