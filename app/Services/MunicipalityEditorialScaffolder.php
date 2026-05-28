<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Models\State;

final class MunicipalityEditorialScaffolder
{
    public function __construct(
        private readonly State $states = new State()
    ) {
    }

    public function buildPayload(array $municipality, ?int $userId = null): array
    {
        $state = $this->states->find((int) $municipality['id_estado']);
        $stateName = $state['nome'] ?? ($municipality['estado_nome'] ?? 'o estado');
        $stateRegion = $state['regiao'] ?? 'a regiao';
        $name = (string) $municipality['nome'];
        $gentilico = trim((string) ($municipality['gentilico'] ?? '')) !== '' ? (string) $municipality['gentilico'] : 'moradores locais';
        $population = $municipality['populacao'] ? number_format((int) $municipality['populacao'], 0, ',', '.') . ' habitantes' : 'uma populacao em consolidacao no acervo';
        $area = $municipality['area'] ? (string) $municipality['area'] . ' km2' : 'uma area territorial relevante dentro do estado';

        $templates = [
            'descricao_curta' => $name . ' integra o territorio do ' . $stateName . ' e entra no MAPI CONECTA como ponto de partida para conhecer identidade local, memoria e dinamicas do municipio.',
            'historia' => 'A trajetoria de ' . $name . ' ainda sera aprofundada pela curadoria, mas esta secao ja sinaliza a importancia de reunir origem do povoamento, mudancas administrativas e marcos da memoria local para contextualizar o municipio.',
            'geografia' => $name . ' pertence ao estado do ' . $stateName . ', na regiao ' . $stateRegion . ', e ocupa ' . $area . '. Esta secao deve evoluir com informacoes sobre relevo, clima, hidrografia, localizacao e relacao com municipios vizinhos.',
            'economia' => 'A leitura economica inicial de ' . $name . ' deve considerar producao local, servicos, comercio e atividades que ajudam a sustentar o cotidiano de ' . $gentilico . '. O dado populacional atualmente registrado e de ' . $population . '.',
            'cultura' => 'A cultura de ' . $name . ' pode ser apresentada a partir de festas, manifestacoes populares, culinaria, memoria oral, artistas e praticas que fortalecem o pertencimento de ' . $gentilico . '.',
            'turismo' => 'O potencial turistico de ' . $name . ' ainda sera refinado, mas esta secao deve reunir lugares de interesse, patrimonios, paisagens, eventos e experiencias que ajudem visitantes e moradores a circular pelo municipio com contexto.',
            'educacao' => 'Na frente educativa, ' . $name . ' pode ser usado como base para atividades sobre territorio, identidade local, leitura de mapas, patrimonio cultural e estudo interdisciplinar conectado ao cotidiano.',
            'curiosidades' => 'Esta secao pode reunir fatos pouco conhecidos, apelidos, personagens, acontecimentos marcantes e detalhes do cotidiano que tornem a descoberta de ' . $name . ' mais memoravel.',
        ];

        $payload = [
            'id_estado' => (int) $municipality['id_estado'],
            'codigo_ibge' => (int) $municipality['codigo_ibge'],
            'nome' => $municipality['nome'],
            'slug' => $municipality['slug'],
            'descricao_curta' => $this->shouldReplacePlaceholder($municipality['descricao_curta'] ?? null) ? $templates['descricao_curta'] : (string) ($municipality['descricao_curta'] ?? ''),
            'historia' => $this->shouldReplacePlaceholder($municipality['historia'] ?? null) ? $templates['historia'] : (string) ($municipality['historia'] ?? ''),
            'geografia' => $this->shouldReplacePlaceholder($municipality['geografia'] ?? null) ? $templates['geografia'] : (string) ($municipality['geografia'] ?? ''),
            'economia' => $this->shouldReplacePlaceholder($municipality['economia'] ?? null) ? $templates['economia'] : (string) ($municipality['economia'] ?? ''),
            'cultura' => $this->shouldReplacePlaceholder($municipality['cultura'] ?? null) ? $templates['cultura'] : (string) ($municipality['cultura'] ?? ''),
            'turismo' => $this->shouldReplacePlaceholder($municipality['turismo'] ?? null) ? $templates['turismo'] : (string) ($municipality['turismo'] ?? ''),
            'educacao' => $this->shouldReplacePlaceholder($municipality['educacao'] ?? null) ? $templates['educacao'] : (string) ($municipality['educacao'] ?? ''),
            'curiosidades' => $this->shouldReplacePlaceholder($municipality['curiosidades'] ?? null) ? $templates['curiosidades'] : (string) ($municipality['curiosidades'] ?? ''),
            'populacao' => (string) ($municipality['populacao'] ?? ''),
            'area' => (string) ($municipality['area'] ?? ''),
            'densidade_demografica' => (string) ($municipality['densidade_demografica'] ?? ''),
            'gentilico' => (string) ($municipality['gentilico'] ?? ''),
            'data_fundacao' => (string) ($municipality['data_fundacao'] ?? ''),
            'latitude' => (string) ($municipality['latitude'] ?? ''),
            'longitude' => (string) ($municipality['longitude'] ?? ''),
            'distancia_capital' => (string) ($municipality['distancia_capital'] ?? ''),
            'imagem_principal' => (string) ($municipality['imagem_principal'] ?? ''),
            'fonte_dados' => (string) ($municipality['fonte_dados'] ?? ''),
            'status_curadoria' => $municipality['status_curadoria'] ?? 'rascunho',
            'status_publicacao' => $municipality['status_publicacao'] ?? 'rascunho',
            'criado_por' => $municipality['criado_por'] ?? null,
            'atualizado_por' => $userId ?? (Auth::user()['id'] ?? null),
            'revisado_por' => $municipality['revisado_por'] ?? null,
            'publicado_por' => $municipality['publicado_por'] ?? null,
            'publicado_em' => $municipality['publicado_em'] ?? null,
        ];

        if (($payload['status_curadoria'] ?? 'rascunho') === 'rascunho') {
            $payload['status_curadoria'] = 'em_correcao';
        }

        return $payload;
    }

    public function shouldReplacePlaceholder(mixed $value): bool
    {
        $normalized = trim((string) $value);
        if ($normalized === '') {
            return true;
        }

        $patterns = [
            'faz parte do acervo inicial do mapi conecta',
            'conteudo geografico inicial para',
            'conteudo cultural inicial para',
            'conteudo turistico inicial para',
            'conteudo ainda nao publicado nesta secao',
            'conteudo editorial em expansao para este municipio',
        ];

        $normalized = mb_strtolower($normalized, 'UTF-8');
        foreach ($patterns as $pattern) {
            if (str_contains($normalized, $pattern)) {
                return true;
            }
        }

        return false;
    }
}
