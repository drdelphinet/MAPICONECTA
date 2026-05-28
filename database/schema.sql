CREATE DATABASE IF NOT EXISTS mapiconecta CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mapiconecta;

CREATE TABLE perfis (
    id_perfil INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    descricao VARCHAR(255) NULL,
    escopo ENUM('publico', 'administrativo') NOT NULL DEFAULT 'publico',
    criado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE usuarios (
    id_usuario INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_perfil INT UNSIGNED NOT NULL,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    status ENUM('ativo', 'inativo', 'bloqueado') NOT NULL DEFAULT 'ativo',
    ultimo_login_em DATETIME NULL,
    criado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuarios_perfil FOREIGN KEY (id_perfil) REFERENCES perfis (id_perfil)
);

CREATE TABLE estados (
    id_estado INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo_ibge INT UNSIGNED NOT NULL UNIQUE,
    nome VARCHAR(120) NOT NULL,
    sigla CHAR(2) NOT NULL UNIQUE,
    regiao VARCHAR(100) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    status ENUM('rascunho', 'ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    criado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE regioes_turisticas (
    id_regiao_turistica INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_estado INT UNSIGNED NOT NULL,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT NULL,
    status ENUM('rascunho', 'ativo', 'inativo') NOT NULL DEFAULT 'rascunho',
    criado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_regioes_turisticas_estado FOREIGN KEY (id_estado) REFERENCES estados (id_estado)
);

CREATE TABLE municipios (
    id_municipio INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_estado INT UNSIGNED NOT NULL,
    codigo_ibge INT UNSIGNED NOT NULL UNIQUE,
    nome VARCHAR(150) NOT NULL,
    slug VARCHAR(180) NOT NULL UNIQUE,
    descricao_curta TEXT NULL,
    historia LONGTEXT NULL,
    geografia LONGTEXT NULL,
    economia LONGTEXT NULL,
    cultura LONGTEXT NULL,
    turismo LONGTEXT NULL,
    educacao LONGTEXT NULL,
    curiosidades LONGTEXT NULL,
    populacao BIGINT NULL,
    area DECIMAL(12,2) NULL,
    densidade_demografica DECIMAL(10,2) NULL,
    gentilico VARCHAR(120) NULL,
    data_fundacao DATE NULL,
    latitude DECIMAL(10,7) NULL,
    longitude DECIMAL(10,7) NULL,
    distancia_capital DECIMAL(10,2) NULL,
    imagem_principal VARCHAR(255) NULL,
    fonte_dados VARCHAR(255) NULL,
    status_curadoria ENUM('rascunho', 'enviado_revisao', 'em_correcao', 'aprovado', 'publicado', 'reprovado', 'arquivado') NOT NULL DEFAULT 'rascunho',
    status_publicacao ENUM('rascunho', 'publicado', 'despublicado') NOT NULL DEFAULT 'rascunho',
    criado_por INT UNSIGNED NULL,
    atualizado_por INT UNSIGNED NULL,
    revisado_por INT UNSIGNED NULL,
    publicado_por INT UNSIGNED NULL,
    criado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    publicado_em DATETIME NULL,
    CONSTRAINT fk_municipios_estado FOREIGN KEY (id_estado) REFERENCES estados (id_estado),
    CONSTRAINT fk_municipios_criado_por FOREIGN KEY (criado_por) REFERENCES usuarios (id_usuario),
    CONSTRAINT fk_municipios_atualizado_por FOREIGN KEY (atualizado_por) REFERENCES usuarios (id_usuario),
    CONSTRAINT fk_municipios_revisado_por FOREIGN KEY (revisado_por) REFERENCES usuarios (id_usuario),
    CONSTRAINT fk_municipios_publicado_por FOREIGN KEY (publicado_por) REFERENCES usuarios (id_usuario)
);

CREATE TABLE municipio_regiao_turistica (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_municipio INT UNSIGNED NOT NULL,
    id_regiao_turistica INT UNSIGNED NOT NULL,
    CONSTRAINT fk_municipio_regiao_municipio FOREIGN KEY (id_municipio) REFERENCES municipios (id_municipio),
    CONSTRAINT fk_municipio_regiao_regiao FOREIGN KEY (id_regiao_turistica) REFERENCES regioes_turisticas (id_regiao_turistica),
    UNIQUE KEY uk_municipio_regiao (id_municipio, id_regiao_turistica)
);

CREATE TABLE indicadores_municipais (
    id_indicador INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_municipio INT UNSIGNED NOT NULL,
    fonte VARCHAR(120) NOT NULL,
    codigo_tabela VARCHAR(100) NULL,
    nome_indicador VARCHAR(180) NOT NULL,
    valor DECIMAL(18,4) NULL,
    unidade VARCHAR(50) NULL,
    ano YEAR NOT NULL,
    data_importacao DATETIME NOT NULL,
    observacao TEXT NULL,
    CONSTRAINT fk_indicadores_municipio FOREIGN KEY (id_municipio) REFERENCES municipios (id_municipio)
);

CREATE TABLE quizzes (
    id_quiz INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_municipio INT UNSIGNED NOT NULL,
    titulo VARCHAR(180) NOT NULL,
    descricao TEXT NULL,
    status_curadoria ENUM('rascunho', 'enviado_revisao', 'em_correcao', 'aprovado', 'publicado', 'reprovado', 'arquivado') NOT NULL DEFAULT 'rascunho',
    criado_por INT UNSIGNED NULL,
    revisado_por INT UNSIGNED NULL,
    publicado_por INT UNSIGNED NULL,
    criado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    publicado_em DATETIME NULL,
    CONSTRAINT fk_quizzes_municipio FOREIGN KEY (id_municipio) REFERENCES municipios (id_municipio),
    CONSTRAINT fk_quizzes_criado_por FOREIGN KEY (criado_por) REFERENCES usuarios (id_usuario),
    CONSTRAINT fk_quizzes_revisado_por FOREIGN KEY (revisado_por) REFERENCES usuarios (id_usuario),
    CONSTRAINT fk_quizzes_publicado_por FOREIGN KEY (publicado_por) REFERENCES usuarios (id_usuario)
);

CREATE TABLE perguntas_quiz (
    id_pergunta INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_quiz INT UNSIGNED NOT NULL,
    pergunta TEXT NOT NULL,
    alternativa_a VARCHAR(255) NOT NULL,
    alternativa_b VARCHAR(255) NOT NULL,
    alternativa_c VARCHAR(255) NOT NULL,
    alternativa_d VARCHAR(255) NOT NULL,
    resposta_correta ENUM('a', 'b', 'c', 'd') NOT NULL,
    explicacao TEXT NULL,
    pontos INT UNSIGNED NOT NULL DEFAULT 10,
    ordem INT UNSIGNED NOT NULL DEFAULT 1,
    status_curadoria ENUM('rascunho', 'enviado_revisao', 'em_correcao', 'aprovado', 'publicado', 'reprovado', 'arquivado') NOT NULL DEFAULT 'rascunho',
    criado_por INT UNSIGNED NULL,
    revisado_por INT UNSIGNED NULL,
    publicado_por INT UNSIGNED NULL,
    criado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_perguntas_quiz FOREIGN KEY (id_quiz) REFERENCES quizzes (id_quiz),
    CONSTRAINT fk_perguntas_criado_por FOREIGN KEY (criado_por) REFERENCES usuarios (id_usuario),
    CONSTRAINT fk_perguntas_revisado_por FOREIGN KEY (revisado_por) REFERENCES usuarios (id_usuario),
    CONSTRAINT fk_perguntas_publicado_por FOREIGN KEY (publicado_por) REFERENCES usuarios (id_usuario)
);

CREATE TABLE progresso_quiz_usuario (
    id_progresso INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NOT NULL,
    id_quiz INT UNSIGNED NOT NULL,
    pontuacao_total INT UNSIGNED NOT NULL DEFAULT 0,
    total_acertos INT UNSIGNED NOT NULL DEFAULT 0,
    total_perguntas INT UNSIGNED NOT NULL DEFAULT 0,
    concluido_em DATETIME NULL,
    atualizado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_progresso_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario),
    CONSTRAINT fk_progresso_quiz FOREIGN KEY (id_quiz) REFERENCES quizzes (id_quiz),
    UNIQUE KEY uk_progresso_usuario_quiz (id_usuario, id_quiz)
);

CREATE TABLE respostas_quiz_usuario (
    id_resposta BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NOT NULL,
    id_quiz INT UNSIGNED NOT NULL,
    id_pergunta INT UNSIGNED NOT NULL,
    resposta_marcada ENUM('a', 'b', 'c', 'd') NOT NULL,
    resposta_correta ENUM('a', 'b', 'c', 'd') NOT NULL,
    acertou TINYINT(1) NOT NULL DEFAULT 0,
    pontos_obtidos INT UNSIGNED NOT NULL DEFAULT 0,
    respondido_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_respostas_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario),
    CONSTRAINT fk_respostas_quiz FOREIGN KEY (id_quiz) REFERENCES quizzes (id_quiz),
    CONSTRAINT fk_respostas_pergunta FOREIGN KEY (id_pergunta) REFERENCES perguntas_quiz (id_pergunta)
);

CREATE TABLE municipios_favoritos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NOT NULL,
    id_municipio INT UNSIGNED NOT NULL,
    criado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_favoritos_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario),
    CONSTRAINT fk_favoritos_municipio FOREIGN KEY (id_municipio) REFERENCES municipios (id_municipio),
    UNIQUE KEY uk_favorito_usuario_municipio (id_usuario, id_municipio)
);

CREATE TABLE municipios_visitados (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NOT NULL,
    id_municipio INT UNSIGNED NOT NULL,
    visitado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    origem ENUM('manual', 'qrcode', 'quiz', 'sistema') NOT NULL DEFAULT 'manual',
    CONSTRAINT fk_visitados_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario),
    CONSTRAINT fk_visitados_municipio FOREIGN KEY (id_municipio) REFERENCES municipios (id_municipio),
    UNIQUE KEY uk_visitado_usuario_municipio (id_usuario, id_municipio)
);

CREATE TABLE medalhas (
    id_medalha INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    descricao TEXT NULL,
    pontos_necessarios INT UNSIGNED NOT NULL DEFAULT 0,
    criado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE usuarios_medalhas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NOT NULL,
    id_medalha INT UNSIGNED NOT NULL,
    conquistada_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuarios_medalhas_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario),
    CONSTRAINT fk_usuarios_medalhas_medalha FOREIGN KEY (id_medalha) REFERENCES medalhas (id_medalha),
    UNIQUE KEY uk_usuario_medalha (id_usuario, id_medalha)
);

CREATE TABLE pontos_turisticos (
    id_ponto_turistico INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_municipio INT UNSIGNED NOT NULL,
    nome VARCHAR(180) NOT NULL,
    categoria VARCHAR(100) NULL,
    descricao TEXT NULL,
    endereco VARCHAR(255) NULL,
    latitude DECIMAL(10,7) NULL,
    longitude DECIMAL(10,7) NULL,
    horario_funcionamento VARCHAR(150) NULL,
    valor_entrada VARCHAR(120) NULL,
    contato VARCHAR(150) NULL,
    imagem VARCHAR(255) NULL,
    fonte VARCHAR(255) NULL,
    status_curadoria ENUM('rascunho', 'enviado_revisao', 'em_correcao', 'aprovado', 'publicado', 'reprovado', 'arquivado') NOT NULL DEFAULT 'rascunho',
    criado_por INT UNSIGNED NULL,
    revisado_por INT UNSIGNED NULL,
    publicado_por INT UNSIGNED NULL,
    criado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pontos_turisticos_municipio FOREIGN KEY (id_municipio) REFERENCES municipios (id_municipio),
    CONSTRAINT fk_pontos_turisticos_criado_por FOREIGN KEY (criado_por) REFERENCES usuarios (id_usuario),
    CONSTRAINT fk_pontos_turisticos_revisado_por FOREIGN KEY (revisado_por) REFERENCES usuarios (id_usuario),
    CONSTRAINT fk_pontos_turisticos_publicado_por FOREIGN KEY (publicado_por) REFERENCES usuarios (id_usuario)
);

CREATE TABLE midias_municipio (
    id_midia INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_municipio INT UNSIGNED NOT NULL,
    titulo VARCHAR(180) NOT NULL,
    descricao TEXT NULL,
    tipo ENUM('imagem', 'video', 'audio', 'documento') NOT NULL DEFAULT 'imagem',
    url_arquivo VARCHAR(255) NOT NULL,
    credito VARCHAR(180) NULL,
    texto_alternativo VARCHAR(255) NULL,
    licenca VARCHAR(120) NULL,
    url_origem VARCHAR(255) NULL,
    categoria_visual VARCHAR(40) NULL,
    destaque TINYINT(1) NOT NULL DEFAULT 0,
    status_curadoria ENUM('rascunho', 'enviado_revisao', 'em_correcao', 'aprovado', 'publicado', 'reprovado', 'arquivado') NOT NULL DEFAULT 'rascunho',
    criado_por INT UNSIGNED NULL,
    revisado_por INT UNSIGNED NULL,
    publicado_por INT UNSIGNED NULL,
    criado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_midias_municipio FOREIGN KEY (id_municipio) REFERENCES municipios (id_municipio),
    CONSTRAINT fk_midias_criado_por FOREIGN KEY (criado_por) REFERENCES usuarios (id_usuario),
    CONSTRAINT fk_midias_revisado_por FOREIGN KEY (revisado_por) REFERENCES usuarios (id_usuario),
    CONSTRAINT fk_midias_publicado_por FOREIGN KEY (publicado_por) REFERENCES usuarios (id_usuario)
);

CREATE TABLE atividades_pedagogicas (
    id_atividade INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_municipio INT UNSIGNED NOT NULL,
    titulo VARCHAR(180) NOT NULL,
    descricao TEXT NULL,
    disciplina VARCHAR(120) NOT NULL,
    ano_escolar VARCHAR(80) NULL,
    objetivo TEXT NULL,
    metodologia TEXT NULL,
    recursos_necessarios TEXT NULL,
    desenvolvimento LONGTEXT NULL,
    avaliacao TEXT NULL,
    arquivo_pdf VARCHAR(255) NULL,
    status_curadoria ENUM('rascunho', 'enviado_revisao', 'em_correcao', 'aprovado', 'publicado', 'reprovado', 'arquivado') NOT NULL DEFAULT 'rascunho',
    criado_por INT UNSIGNED NULL,
    revisado_por INT UNSIGNED NULL,
    publicado_por INT UNSIGNED NULL,
    criado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_atividades_municipio FOREIGN KEY (id_municipio) REFERENCES municipios (id_municipio),
    CONSTRAINT fk_atividades_criado_por FOREIGN KEY (criado_por) REFERENCES usuarios (id_usuario),
    CONSTRAINT fk_atividades_revisado_por FOREIGN KEY (revisado_por) REFERENCES usuarios (id_usuario),
    CONSTRAINT fk_atividades_publicado_por FOREIGN KEY (publicado_por) REFERENCES usuarios (id_usuario)
);

CREATE TABLE qrcodes (
    id_qrcode INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entidade_tipo VARCHAR(80) NOT NULL,
    entidade_id INT UNSIGNED NOT NULL,
    url_destino VARCHAR(255) NOT NULL,
    arquivo_qrcode VARCHAR(255) NULL,
    total_acessos INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    criado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE qrcode_acessos (
    id_acesso BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_qrcode INT UNSIGNED NOT NULL,
    ip_hash CHAR(64) NULL,
    user_agent VARCHAR(255) NULL,
    origem VARCHAR(100) NULL,
    data_acesso DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_qrcode_acessos_qrcode FOREIGN KEY (id_qrcode) REFERENCES qrcodes (id_qrcode)
);

CREATE TABLE integracao_logs (
    id_log BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fonte VARCHAR(120) NOT NULL,
    endpoint VARCHAR(255) NOT NULL,
    parametros JSON NULL,
    status ENUM('sucesso', 'erro', 'pendente', 'processando') NOT NULL DEFAULT 'pendente',
    mensagem TEXT NULL,
    resposta_resumida TEXT NULL,
    data_execucao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT UNSIGNED NULL,
    CONSTRAINT fk_integracao_logs_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id_usuario)
);

CREATE TABLE historico_curadoria (
    id_historico BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entidade_tipo VARCHAR(80) NOT NULL,
    entidade_id INT UNSIGNED NOT NULL,
    acao VARCHAR(80) NOT NULL,
    status_anterior VARCHAR(80) NULL,
    status_novo VARCHAR(80) NULL,
    observacao TEXT NULL,
    usuario_id INT UNSIGNED NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_historico_curadoria_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id_usuario)
);

INSERT INTO perfis (nome, slug, descricao, escopo) VALUES
('Administrador Geral', 'administrador-geral', 'Perfil com acesso total ao painel administrativo.', 'administrativo'),
('Curador', 'curador', 'Perfil responsavel por revisar e aprovar conteudos.', 'administrativo'),
('Editor', 'editor', 'Perfil responsavel por cadastrar e editar conteudos.', 'administrativo'),
('Usuario', 'usuario', 'Perfil padrao para usuarios da area publica.', 'publico');

INSERT INTO usuarios (id_perfil, nome, email, senha, status) VALUES
(1, 'Administrador Inicial', 'admin@mapiconecta.local', '$2y$10$aNHGGX9Y6MR984c5KlfD1uGfpV8hvfuPNLAKIerHw30cs96w3QG5q', 'ativo');

INSERT INTO estados (codigo_ibge, nome, sigla, regiao, slug, status) VALUES
(22, 'Piaui', 'PI', 'Nordeste', 'piaui', 'ativo');

INSERT INTO municipios (
    id_estado, codigo_ibge, nome, slug, descricao_curta, historia, geografia, economia, cultura, turismo,
    educacao, curiosidades, populacao, area, densidade_demografica, gentilico, data_fundacao, latitude,
    longitude, distancia_capital, imagem_principal, fonte_dados, status_curadoria, status_publicacao,
    criado_por, atualizado_por, publicado_por, publicado_em
) VALUES (
    1, 2211001, 'Teresina', 'teresina',
    'Capital do Piaui e principal porta de entrada inicial do MAPI CONECTA.',
    'Teresina e um centro historico e politico importante do estado.',
    'Municipio localizado no centro-norte do Piaui, em area de encontro de rios.',
    'A economia local se destaca por comercio, servicos e administracao publica.',
    'A cidade reune festas, gastronomia e expressoes culturais marcantes do Piaui.',
    'O municipio pode servir como ponto de partida para roteiros urbanos e regionais.',
    'Possui relevancia educacional e institucional no estado.',
    'Conhecida como Cidade Verde.',
    866300, 1391.98, 622.34, 'teresinense', NULL, -5.0919440,
    -42.8033330, NULL, NULL, 'Seed inicial do projeto', 'publicado', 'publicado',
    1, 1, 1, NOW()
);

INSERT INTO municipios (
    id_estado, codigo_ibge, nome, slug, descricao_curta, historia, geografia, economia, cultura, turismo,
    educacao, curiosidades, populacao, area, densidade_demografica, gentilico, data_fundacao, latitude,
    longitude, distancia_capital, imagem_principal, fonte_dados, status_curadoria, status_publicacao,
    criado_por, atualizado_por, publicado_por, publicado_em
) VALUES (
    1, 2207702, 'Parnaiba', 'parnaiba',
    'Cidade litoranea do Piaui, com forte identidade cultural e relevancia turistica regional.',
    'Parnaiba ocupa lugar importante na historia economica e cultural do norte piauiense.',
    'Municipio localizado no litoral do Piaui, com destaque para delta, praias e conexoes ambientais singulares.',
    'A economia local combina comercio, servicos, turismo e atividades ligadas ao territorio costeiro.',
    'A cidade reune manifestacoes culturais, gastronomia e referencias importantes da identidade litoranea piauiense.',
    'Parnaiba pode servir como base para roteiros de turismo de natureza, cultura e patrimonio local.',
    'O municipio oferece contexto rico para atividades interdisciplinares ligadas a territorio, cultura e turismo.',
    'Parnaiba esta associada ao Delta do Parnaiba, um dos marcos naturais mais conhecidos da regiao.',
    162159, 436.91, 371.15, 'parnaibano', NULL, -2.9055560,
    -41.7769440, NULL, NULL, 'Seed inicial do projeto', 'publicado', 'publicado',
    1, 1, 1, NOW()
);

INSERT INTO medalhas (nome, slug, descricao, pontos_necessarios) VALUES
('Explorador Iniciante', 'explorador-iniciante', 'Concedida ao atingir os primeiros 50 pontos em quizzes.', 50),
('Descobridor do Piaui', 'descobridor-do-piaui', 'Concedida ao atingir 150 pontos em quizzes.', 150),
('Guardiao dos Municipios', 'guardiao-dos-municipios', 'Concedida ao atingir 300 pontos em quizzes.', 300);

INSERT INTO quizzes (id_municipio, titulo, descricao, status_curadoria, criado_por, revisado_por, publicado_por, publicado_em) VALUES
(1, 'Quiz de Teresina', 'Perguntas introdutorias sobre a capital do Piaui.', 'publicado', 1, 1, 1, NOW()),
(2, 'Quiz de Parnaiba', 'Perguntas introdutorias sobre cultura, territorio e turismo de Parnaiba.', 'publicado', 1, 1, 1, NOW());

INSERT INTO perguntas_quiz (
    id_quiz, pergunta, alternativa_a, alternativa_b, alternativa_c, alternativa_d, resposta_correta, explicacao, pontos, ordem, status_curadoria, criado_por, revisado_por, publicado_por
) VALUES
(1, 'Qual apelido popular Teresina recebeu ao longo do tempo?', 'Cidade Azul', 'Cidade Verde', 'Cidade Dourada', 'Cidade das Aguas', 'b', 'Teresina e amplamente conhecida como Cidade Verde.', 10, 1, 'publicado', 1, 1, 1),
(1, 'Teresina pertence a qual estado brasileiro?', 'Ceara', 'Maranhao', 'Piaui', 'Bahia', 'c', 'Teresina e a capital do estado do Piaui.', 10, 2, 'publicado', 1, 1, 1),
(1, 'Qual area recebe destaque inicial no projeto MAPI CONECTA em Teresina?', 'Industria naval', 'Turismo, cultura e educacao territorial', 'Mineracao pesada', 'Exploracao antartica', 'b', 'O projeto conecta turismo, cultura, educacao e exploracao territorial.', 10, 3, 'publicado', 1, 1, 1),
(2, 'Parnaiba pertence a qual estado brasileiro?', 'Piaui', 'Pernambuco', 'Ceara', 'Sergipe', 'a', 'Parnaiba e um municipio do estado do Piaui.', 10, 1, 'publicado', 1, 1, 1),
(2, 'Qual tema se destaca na apresentacao inicial de Parnaiba no projeto?', 'Territorio costeiro e turismo regional', 'Mineracao em larga escala', 'Pantanal sul-mato-grossense', 'Capital federal', 'a', 'O seed destaca o territorio costeiro, a identidade cultural e o turismo regional.', 10, 2, 'publicado', 1, 1, 1),
(2, 'Parnaiba esta associada a qual referencia natural conhecida?', 'Chapada dos Veadeiros', 'Delta do Parnaiba', 'Serra Gaucha', 'Ilha do Bananal', 'b', 'O Delta do Parnaiba e uma das referencias naturais mais conhecidas ligadas ao municipio.', 10, 3, 'publicado', 1, 1, 1);

INSERT INTO atividades_pedagogicas (
    id_municipio, titulo, descricao, disciplina, ano_escolar, objetivo, metodologia, recursos_necessarios,
    desenvolvimento, avaliacao, arquivo_pdf, status_curadoria, criado_por, revisado_por, publicado_por
) VALUES (
    1,
    'Roteiro de estudo: conhecendo Teresina',
    'Atividade introdutoria para explorar historia, geografia e cultura de Teresina com apoio do MAPI CONECTA.',
    'Historia',
    '8o e 9o ano',
    'Identificar elementos importantes da formacao historica e cultural de Teresina dentro do contexto do Piaui.',
    'Leitura orientada da pagina do municipio, conversa guiada em sala e fechamento com quiz.',
    'Celular ou computador com acesso ao MAPI CONECTA, caderno de anotacoes e projetor opcional.',
    'Abrir o municipio de Teresina, ler as secoes principais, registrar tres informacoes importantes e responder o quiz da cidade ao final.',
    'Participacao na discussao e registro das informacoes encontradas durante a exploracao.',
    NULL,
    'publicado',
    1,
    1,
    1
), (
    2,
    'Roteiro de estudo: territorio e cultura em Parnaiba',
    'Atividade introdutoria para explorar litoral, identidade cultural e turismo em Parnaiba com apoio do MAPI CONECTA.',
    'Geografia',
    '9o ano e ensino medio',
    'Relacionar territorio, turismo, cultura e paisagem na leitura do municipio de Parnaiba.',
    'Exploracao da pagina do municipio, observacao do mapa e fechamento com comparacao entre conteudo e quiz.',
    'Celular ou computador com acesso ao MAPI CONECTA, mapa projetado ou impresso e caderno de anotacoes.',
    'Abrir Parnaiba no MAPI CONECTA, ler as secoes principais, destacar tres elementos do territorio local e responder o quiz como fechamento.',
    'Registro das observacoes e participacao nas respostas do quiz.',
    NULL,
    'publicado',
    1,
    1,
    1
);

INSERT INTO pontos_turisticos (
    id_municipio, nome, categoria, descricao, endereco, latitude, longitude, horario_funcionamento,
    valor_entrada, contato, imagem, fonte, status_curadoria, criado_por, revisado_por, publicado_por
) VALUES (
    1,
    'Parque Ambiental Encontro dos Rios',
    'Parque urbano',
    'Area de visita ligada a paisagem dos rios e a leitura ambiental da capital.',
    'Zona Norte, Teresina - PI',
    -5.0385000,
    -42.8015000,
    'Consultar agenda local',
    'Gratuito',
    NULL,
    NULL,
    'Seed inicial do projeto',
    'publicado',
    1,
    1,
    1
), (
    2,
    'Porto das Barcas',
    'Patrimonio historico',
    'Conjunto historico associado a memoria urbana e ao circuito cultural de Parnaiba.',
    'Centro, Parnaiba - PI',
    -2.9049000,
    -41.7763000,
    'Consultar agenda local',
    'Gratuito',
    NULL,
    NULL,
    'Seed inicial do projeto',
    'publicado',
    1,
    1,
    1
);

INSERT INTO midias_municipio (
    id_municipio, titulo, descricao, tipo, url_arquivo, credito, texto_alternativo, licenca, url_origem, categoria_visual, destaque,
    status_curadoria, criado_por, revisado_por, publicado_por
) VALUES (
    1,
    'Vista urbana de Teresina',
    'Imagem institucional de apoio para apresentacao inicial do municipio.',
    'imagem',
    'https://images.unsplash.com/photo-1473448912268-2022ce9509d8?auto=format&fit=crop&w=1200&q=80',
    'Unsplash',
    'Paisagem urbana com destaque para horizonte e vegetacao em cidade brasileira',
    'Licenca Unsplash',
    'https://unsplash.com/photos/1473448912268-2022ce9509d8',
    'paisagem',
    1,
    'publicado',
    1,
    1,
    1
), (
    2,
    'Paisagem de apoio para Parnaiba',
    'Imagem de apoio visual para destacar o ambiente costeiro e turistico.',
    'imagem',
    'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80',
    'Unsplash',
    'Paisagem litoranea com mar e faixa de areia',
    'Licenca Unsplash',
    'https://unsplash.com/photos/1507525428034-b723cf961d3e',
    'paisagem',
    1,
    'publicado',
    1,
    1,
    1
);
