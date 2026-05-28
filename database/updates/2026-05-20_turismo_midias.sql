CREATE TABLE IF NOT EXISTS midias_municipio (
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

INSERT INTO pontos_turisticos (
    id_municipio, nome, categoria, descricao, endereco, latitude, longitude, horario_funcionamento,
    valor_entrada, contato, imagem, fonte, status_curadoria, criado_por, revisado_por, publicado_por
)
SELECT
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
    'Atualizacao incremental do projeto',
    'publicado',
    1,
    1,
    1
WHERE NOT EXISTS (
    SELECT 1 FROM pontos_turisticos WHERE id_municipio = 1 AND nome = 'Parque Ambiental Encontro dos Rios'
);

INSERT INTO pontos_turisticos (
    id_municipio, nome, categoria, descricao, endereco, latitude, longitude, horario_funcionamento,
    valor_entrada, contato, imagem, fonte, status_curadoria, criado_por, revisado_por, publicado_por
)
SELECT
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
    'Atualizacao incremental do projeto',
    'publicado',
    1,
    1,
    1
WHERE NOT EXISTS (
    SELECT 1 FROM pontos_turisticos WHERE id_municipio = 2 AND nome = 'Porto das Barcas'
);

INSERT INTO midias_municipio (
    id_municipio, titulo, descricao, tipo, url_arquivo, credito, texto_alternativo, licenca, url_origem, categoria_visual, destaque,
    status_curadoria, criado_por, revisado_por, publicado_por
)
SELECT
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
WHERE NOT EXISTS (
    SELECT 1 FROM midias_municipio WHERE id_municipio = 1 AND titulo = 'Vista urbana de Teresina'
);

INSERT INTO midias_municipio (
    id_municipio, titulo, descricao, tipo, url_arquivo, credito, texto_alternativo, licenca, url_origem, categoria_visual, destaque,
    status_curadoria, criado_por, revisado_por, publicado_por
)
SELECT
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
WHERE NOT EXISTS (
    SELECT 1 FROM midias_municipio WHERE id_municipio = 2 AND titulo = 'Paisagem de apoio para Parnaiba'
);
