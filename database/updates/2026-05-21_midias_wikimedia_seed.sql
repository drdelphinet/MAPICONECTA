UPDATE municipios
SET imagem_principal = 'https://commons.wikimedia.org/wiki/Special:FilePath/Ponte%20Estaiada%20de%20Teresina.JPG'
WHERE id_municipio = 1
  AND (imagem_principal IS NULL OR imagem_principal = '');

UPDATE municipios
SET imagem_principal = 'https://commons.wikimedia.org/wiki/Special:FilePath/Por%20do%20sol%20na%20Pedra%20do%20Sal%2C%20Parnaiba.jpg'
WHERE id_municipio = 2
  AND (imagem_principal IS NULL OR imagem_principal = '');

UPDATE pontos_turisticos
SET imagem = 'https://commons.wikimedia.org/wiki/Special:FilePath/MauricioPokemon%20EncontroDosRios%20Teresina%20PI%20%2840062495695%29.jpg',
    fonte = 'Wikimedia Commons'
WHERE id_ponto_turistico = 1
  AND (imagem IS NULL OR imagem = '');

UPDATE pontos_turisticos
SET imagem = 'https://commons.wikimedia.org/wiki/Special:FilePath/Vista%20geral%20-%20Porto%20das%20Barcas.jpg',
    fonte = 'Wikimedia Commons'
WHERE id_ponto_turistico = 2
  AND (imagem IS NULL OR imagem = '');

INSERT INTO midias_municipio (
    id_municipio, titulo, descricao, tipo, url_arquivo, credito, texto_alternativo,
    licenca, url_origem, categoria_visual, destaque, status_curadoria,
    criado_por, revisado_por, publicado_por
)
SELECT
    1,
    'Bandeira de Teresina',
    'Simbolo civico do municipio de Teresina.',
    'imagem',
    'https://commons.wikimedia.org/wiki/Special:FilePath/Bandeira%20de%20Teresina.svg',
    'BrCaLeTo / Wikimedia Commons',
    'Bandeira oficial do municipio de Teresina',
    'Public domain in Brazil; CC BY-SA 4.0',
    'https://commons.wikimedia.org/wiki/File:Bandeira_de_Teresina.svg',
    'bandeira',
    0,
    'publicado',
    1,
    1,
    1
WHERE NOT EXISTS (
    SELECT 1 FROM midias_municipio WHERE id_municipio = 1 AND titulo = 'Bandeira de Teresina'
);

INSERT INTO midias_municipio (
    id_municipio, titulo, descricao, tipo, url_arquivo, credito, texto_alternativo,
    licenca, url_origem, categoria_visual, destaque, status_curadoria,
    criado_por, revisado_por, publicado_por
)
SELECT
    1,
    'Brasao de Teresina',
    'Simbolo heraldico do municipio de Teresina.',
    'imagem',
    'https://commons.wikimedia.org/wiki/Special:FilePath/Bras%C3%A3o%20de%20Teresina%20%28correto%29.svg',
    'Cleberson Carlos / Wikimedia Commons',
    'Brasao do municipio de Teresina',
    'CC BY-SA 4.0',
    'https://commons.wikimedia.org/wiki/File:Bras%C3%A3o_de_Teresina_%28correto%29.svg',
    'brasao',
    0,
    'publicado',
    1,
    1,
    1
WHERE NOT EXISTS (
    SELECT 1 FROM midias_municipio WHERE id_municipio = 1 AND titulo = 'Brasao de Teresina'
);

INSERT INTO midias_municipio (
    id_municipio, titulo, descricao, tipo, url_arquivo, credito, texto_alternativo,
    licenca, url_origem, categoria_visual, destaque, status_curadoria,
    criado_por, revisado_por, publicado_por
)
SELECT
    1,
    'Ponte Estaiada de Teresina',
    'Registro visual de um dos pontos turisticos mais reconhecidos da cidade.',
    'imagem',
    'https://commons.wikimedia.org/wiki/Special:FilePath/Ponte%20Estaiada%20de%20Teresina.JPG',
    'Matheus Barbosa da Luz / Wikimedia Commons',
    'Vista da Ponte Estaiada de Teresina',
    'CC BY-SA 3.0',
    'https://commons.wikimedia.org/wiki/File:Ponte_Estaiada_de_Teresina.JPG',
    'atrativo',
    1,
    'publicado',
    1,
    1,
    1
WHERE NOT EXISTS (
    SELECT 1 FROM midias_municipio WHERE id_municipio = 1 AND titulo = 'Ponte Estaiada de Teresina'
);

INSERT INTO midias_municipio (
    id_municipio, titulo, descricao, tipo, url_arquivo, credito, texto_alternativo,
    licenca, url_origem, categoria_visual, destaque, status_curadoria,
    criado_por, revisado_por, publicado_por
)
SELECT
    1,
    'Encontro dos Rios em Teresina',
    'Imagem do atrativo ligado ao encontro dos rios no territorio da capital.',
    'imagem',
    'https://commons.wikimedia.org/wiki/Special:FilePath/MauricioPokemon%20EncontroDosRios%20Teresina%20PI%20%2840062495695%29.jpg',
    'MTur Destinos / Mauricio Pokemon / Wikimedia Commons',
    'Imagem do Encontro dos Rios em Teresina',
    'Public Domain Mark',
    'https://commons.wikimedia.org/wiki/File:MauricioPokemon_EncontroDosRios_Teresina_PI_%2840062495695%29.jpg',
    'atrativo',
    1,
    'publicado',
    1,
    1,
    1
WHERE NOT EXISTS (
    SELECT 1 FROM midias_municipio WHERE id_municipio = 1 AND titulo = 'Encontro dos Rios em Teresina'
);

INSERT INTO midias_municipio (
    id_municipio, titulo, descricao, tipo, url_arquivo, credito, texto_alternativo,
    licenca, url_origem, categoria_visual, destaque, status_curadoria,
    criado_por, revisado_por, publicado_por
)
SELECT
    2,
    'Bandeira de Parnaiba',
    'Simbolo civico do municipio de Parnaiba.',
    'imagem',
    'https://commons.wikimedia.org/wiki/Special:FilePath/Bandeira%20Parna%C3%ADba.jpg',
    'Raphael.lorenzeto / Wikimedia Commons',
    'Bandeira oficial do municipio de Parnaiba',
    'Public domain in Brazil',
    'https://commons.wikimedia.org/wiki/File:Bandeira_Parna%C3%ADba.jpg',
    'bandeira',
    0,
    'publicado',
    1,
    1,
    1
WHERE NOT EXISTS (
    SELECT 1 FROM midias_municipio WHERE id_municipio = 2 AND titulo = 'Bandeira de Parnaiba'
);

INSERT INTO midias_municipio (
    id_municipio, titulo, descricao, tipo, url_arquivo, credito, texto_alternativo,
    licenca, url_origem, categoria_visual, destaque, status_curadoria,
    criado_por, revisado_por, publicado_por
)
SELECT
    2,
    'Brasao de Parnaiba',
    'Simbolo heraldico do municipio de Parnaiba.',
    'imagem',
    'https://commons.wikimedia.org/wiki/Special:FilePath/Brasaophb.jpg',
    'Municipio de Parnaiba / Wikimedia Commons',
    'Brasao do municipio de Parnaiba',
    'Public domain in Brazil',
    'https://commons.wikimedia.org/wiki/File:Brasaophb.jpg',
    'brasao',
    0,
    'publicado',
    1,
    1,
    1
WHERE NOT EXISTS (
    SELECT 1 FROM midias_municipio WHERE id_municipio = 2 AND titulo = 'Brasao de Parnaiba'
);

INSERT INTO midias_municipio (
    id_municipio, titulo, descricao, tipo, url_arquivo, credito, texto_alternativo,
    licenca, url_origem, categoria_visual, destaque, status_curadoria,
    criado_por, revisado_por, publicado_por
)
SELECT
    2,
    'Vista geral do Porto das Barcas',
    'Registro do conjunto historico e cultural do Porto das Barcas em Parnaiba.',
    'imagem',
    'https://commons.wikimedia.org/wiki/Special:FilePath/Vista%20geral%20-%20Porto%20das%20Barcas.jpg',
    'GLandovsky / Wikimedia Commons',
    'Vista geral do Porto das Barcas em Parnaiba',
    'CC BY-SA 4.0',
    'https://commons.wikimedia.org/wiki/File:Vista_geral_-_Porto_das_Barcas.jpg',
    'patrimonio',
    1,
    'publicado',
    1,
    1,
    1
WHERE NOT EXISTS (
    SELECT 1 FROM midias_municipio WHERE id_municipio = 2 AND titulo = 'Vista geral do Porto das Barcas'
);

INSERT INTO midias_municipio (
    id_municipio, titulo, descricao, tipo, url_arquivo, credito, texto_alternativo,
    licenca, url_origem, categoria_visual, destaque, status_curadoria,
    criado_por, revisado_por, publicado_por
)
SELECT
    2,
    'Por do sol na Pedra do Sal',
    'Cena costeira que ajuda a apresentar a paisagem de Parnaiba.',
    'imagem',
    'https://commons.wikimedia.org/wiki/Special:FilePath/Por%20do%20sol%20na%20Pedra%20do%20Sal%2C%20Parnaiba.jpg',
    'Robertooaraujo / Wikimedia Commons',
    'Por do sol na Pedra do Sal em Parnaiba',
    'CC BY-SA 4.0',
    'https://commons.wikimedia.org/wiki/File:Por_do_sol_na_Pedra_do_Sal%2C_Parnaiba.jpg',
    'paisagem',
    1,
    'publicado',
    1,
    1,
    1
WHERE NOT EXISTS (
    SELECT 1 FROM midias_municipio WHERE id_municipio = 2 AND titulo = 'Por do sol na Pedra do Sal'
);
