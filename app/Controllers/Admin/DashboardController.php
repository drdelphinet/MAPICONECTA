<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Models\EducationActivity;
use App\Models\Municipality;
use App\Models\MunicipalityMedia;
use App\Models\PointOfInterest;
use App\Models\Quiz;
use PDO;

final class DashboardController extends Controller
{
    public function __construct(
        private readonly Municipality $municipalities = new Municipality(),
        private readonly Quiz $quizzes = new Quiz(),
        private readonly EducationActivity $activities = new EducationActivity(),
        private readonly PointOfInterest $points = new PointOfInterest(),
        private readonly MunicipalityMedia $media = new MunicipalityMedia()
    ) {
    }

    public function index(Request $request): void
    {
        $pdo = Database::connection();

        $stats = [
            'tabelas_banco' => Database::tableExists('midias_municipio') ? 22 : 21,
            'estados' => $this->count($pdo, 'estados'),
            'municipios_oficiais' => $this->municipalities->officialCount('piaui'),
            'municipios' => $this->count($pdo, 'municipios'),
            'usuarios' => $this->count($pdo, 'usuarios'),
            'quizzes' => $this->count($pdo, 'quizzes'),
            'perguntas_quiz' => $this->count($pdo, 'perguntas_quiz'),
            'atividades_pedagogicas' => $this->count($pdo, 'atividades_pedagogicas'),
            'municipios_publicados' => $this->countWhere($pdo, 'municipios', "status_publicacao = 'publicado'"),
            'municipios_com_coordenadas' => $this->countWhere($pdo, 'municipios', "status_publicacao = 'publicado' AND latitude IS NOT NULL AND longitude IS NOT NULL"),
            'municipios_sem_coordenadas' => $this->countWhere($pdo, 'municipios', "status_publicacao = 'publicado' AND (latitude IS NULL OR longitude IS NULL)"),
            'municipios_pendentes' => $this->countWhere($pdo, 'municipios', "status_curadoria IN ('enviado_revisao','em_correcao')"),
            'pontos_turisticos' => $this->count($pdo, 'pontos_turisticos'),
            'indicadores_municipais' => $this->count($pdo, 'indicadores_municipais'),
            'favoritos' => $this->count($pdo, 'municipios_favoritos'),
            'visitados' => $this->count($pdo, 'municipios_visitados'),
            'respostas_quiz' => $this->count($pdo, 'respostas_quiz_usuario'),
            'progresso_quiz' => $this->count($pdo, 'progresso_quiz_usuario'),
            'qrcodes' => $this->count($pdo, 'qrcodes'),
            'qrcode_acessos' => $this->count($pdo, 'qrcode_acessos'),
            'logs_integracao' => $this->count($pdo, 'integracao_logs'),
            'historico_curadoria' => $this->count($pdo, 'historico_curadoria'),
            'conteudos_pendentes' => $this->countWhere($pdo, 'municipios', "status_curadoria IN ('rascunho','enviado_revisao','em_correcao')"),
            'midias_municipio' => Database::tableExists('midias_municipio') ? $this->count($pdo, 'midias_municipio') : 0,
        ];
        $stats['cobertura_mapa_percentual'] = $stats['municipios_publicados'] > 0
            ? (int) floor(($stats['municipios_com_coordenadas'] / $stats['municipios_publicados']) * 100)
            : 0;
        $stats['cobertura_mapa_oficial_percentual'] = $stats['municipios_oficiais'] > 0
            ? (int) floor(($stats['municipios_com_coordenadas'] / $stats['municipios_oficiais']) * 100)
            : 0;

        $this->view('admin/dashboard', [
            'title' => 'Painel Administrativo',
            'user' => Auth::user(),
            'stats' => $stats,
            'workflowSummary' => $this->municipalities->workflowSummary(),
            'missingGeoMunicipalities' => $this->municipalities->publicMissingFromGeoJson(),
            'recentMunicipalities' => array_slice($this->municipalities->adminList(), 0, 5),
            'recentQuizzes' => $this->quizzes->recentAdmin(),
            'recentActivities' => $this->activities->recentAdmin(),
            'recentPoints' => $this->points->recentAdmin(),
            'recentMedia' => $this->media->recentAdmin(),
            'dataDomains' => $this->dataDomains(),
            'collectionSources' => $this->collectionSources(),
            'projectFlow' => $this->projectFlow(),
        ], 'layouts/admin');
    }

    private function count(PDO $pdo, string $table): int
    {
        return (int) $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
    }

    private function countWhere(PDO $pdo, string $table, string $where): int
    {
        return (int) $pdo->query("SELECT COUNT(*) FROM {$table} WHERE {$where}")->fetchColumn();
    }

    private function dataDomains(): array
    {
        return [
            [
                'title' => 'Identidade, acesso e perfis',
                'description' => 'Camada que controla quem entra no sistema, em qual papel e com qual nivel de permissao.',
                'tables' => ['perfis', 'usuarios'],
                'captures' => [
                    'nome, email, senha e status do usuario',
                    'perfil publico ou administrativo',
                    'ultimo login e trilha basica de acesso',
                ],
            ],
            [
                'title' => 'Base territorial e editorial',
                'description' => 'Nucleo central do projeto, onde cada municipio vira uma pagina com contexto geografico, historico, cultural e turistico.',
                'tables' => ['estados', 'regioes_turisticas', 'municipios', 'municipio_regiao_turistica', 'indicadores_municipais', 'pontos_turisticos', 'midias_municipio'],
                'captures' => [
                    'codigo IBGE, nome, sigla, slug e regiao',
                    'historia, geografia, economia, cultura, turismo, educacao e curiosidades',
                    'populacao, area, densidade, gentilico, coordenadas e fontes',
                    'vinculo com regiao turistica, pontos de interesse e galeria de midias',
                ],
            ],
            [
                'title' => 'Curadoria e governanca',
                'description' => 'Camada que evita publicacao automatica sem revisao e preserva a rastreabilidade editorial.',
                'tables' => ['historico_curadoria'],
                'captures' => [
                    'status anterior e novo status por entidade',
                    'acao executada, observacao interna e responsavel',
                    'linha do tempo de aprovacao, correcao, publicacao ou arquivamento',
                ],
            ],
            [
                'title' => 'Educacao e conteudo de apoio',
                'description' => 'Modulo que transforma a base dos municipios em uso pedagogico para escola, professor e estudante.',
                'tables' => ['atividades_pedagogicas'],
                'captures' => [
                    'disciplina, ano escolar e objetivos',
                    'metodologia, recursos, desenvolvimento e avaliacao',
                    'arquivo PDF vinculado e status de curadoria',
                ],
            ],
            [
                'title' => 'Quizzes, gamificacao e jornada do usuario',
                'description' => 'Camada de engajamento que registra interacao, aprendizado, progresso e reconhecimento.',
                'tables' => ['quizzes', 'perguntas_quiz', 'progresso_quiz_usuario', 'respostas_quiz_usuario', 'medalhas', 'usuarios_medalhas', 'municipios_favoritos', 'municipios_visitados'],
                'captures' => [
                    'quiz por municipio, perguntas, alternativas, resposta correta e pontuacao',
                    'respostas marcadas, acertos, pontos e conclusao',
                    'favoritos, visitados, origem da visita e medalhas conquistadas',
                ],
            ],
            [
                'title' => 'QR Codes, integracoes e inteligencia operacional',
                'description' => 'Camada que conecta o produto com distribuicao fisica, importacoes externas e leitura de operacao.',
                'tables' => ['qrcodes', 'qrcode_acessos', 'integracao_logs'],
                'captures' => [
                    'QR Code por entidade, URL de destino e total de acessos',
                    'acessos por QR com hash de IP, user agent, origem e data',
                    'fonte externa, endpoint, parametros, status e mensagem de integracao',
                ],
            ],
        ];
    }

    private function collectionSources(): array
    {
        return [
            [
                'title' => 'Cadastro manual e curadoria interna',
                'description' => 'Editores e curadores alimentam e refinam o acervo com textos, atividades, quizzes e publicacoes.',
            ],
            [
                'title' => 'APIs publicas do IBGE',
                'description' => 'Estrutura prevista para importar estados, municipios, codigos oficiais, indicadores, coordenadas e malhas.',
            ],
            [
                'title' => 'Fontes auxiliares externas',
                'description' => 'OpenStreetMap, Wikidata e outras referencias podem sugerir pontos e complementos, sempre entrando como pendentes.',
            ],
            [
                'title' => 'Interacao do usuario final',
                'description' => 'Favoritos, municipios visitados, respostas de quiz, progresso, ranking e acessos via QR retroalimentam a operacao.',
            ],
        ];
    }

    private function projectFlow(): array
    {
        return [
            [
                'step' => '01',
                'title' => 'Coleta e entrada',
                'description' => 'Dados entram por cadastro interno, seeds, importacao planejada de APIs publicas e futuras planilhas de apoio.',
                'outputs' => ['estados', 'municipios', 'indicadores', 'pontos', 'conteudos base'],
            ],
            [
                'step' => '02',
                'title' => 'Estruturacao no banco',
                'description' => 'Cada dado e salvo em tabelas separadas por dominio para manter relacao entre territorio, conteudo, educacao, quiz e usuario.',
                'outputs' => ['relacoes por municipio', 'historico por entidade', 'trilha de autores e revisores'],
            ],
            [
                'step' => '03',
                'title' => 'Curadoria obrigatoria',
                'description' => 'Conteudos passam por rascunho, revisao, correcao, aprovacao e publicacao antes de chegar na area publica.',
                'outputs' => ['status_curadoria', 'status_publicacao', 'historico_curadoria'],
            ],
            [
                'step' => '04',
                'title' => 'Distribuicao no produto',
                'description' => 'Depois de publicado, o dado abastece mapa, paginas de municipios, area educacional, quizzes e QR Codes.',
                'outputs' => ['mapa publico', 'pagina municipal', 'MAPI Educacao', 'quizzes', 'QR Codes'],
            ],
            [
                'step' => '05',
                'title' => 'Engajamento e retorno',
                'description' => 'A experiencia do usuario gera novos registros para favoritos, visitados, progresso, respostas e acessos.',
                'outputs' => ['favoritos', 'visitados', 'respostas', 'pontuacao', 'acessos QR'],
            ],
            [
                'step' => '06',
                'title' => 'Leitura gerencial',
                'description' => 'O painel transforma essas coletas em indicadores de cobertura, fila editorial, operacao e crescimento do acervo.',
                'outputs' => ['dashboard', 'resumo editorial', 'logs', 'gargalos operacionais'],
            ],
        ];
    }
}
