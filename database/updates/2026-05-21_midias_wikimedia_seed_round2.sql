UPDATE municipios
SET imagem_principal = 'https://commons.wikimedia.org/wiki/Special:FilePath/Floriano%201954.3.jpg'
WHERE id_municipio = 85
  AND (imagem_principal IS NULL OR imagem_principal = '');

UPDATE municipios
SET imagem_principal = 'https://commons.wikimedia.org/wiki/Special:FilePath/Panor%C3%A2mica%2030.jpg'
WHERE id_municipio = 161
  AND (imagem_principal IS NULL OR imagem_principal = '');

UPDATE municipios
SET imagem_principal = 'https://commons.wikimedia.org/wiki/Special:FilePath/Palacete%20da%20antiga%20prefeitura%20%28Piripiri%2C%20Piau%C3%AD%29.jpg'
WHERE id_municipio = 165
  AND (imagem_principal IS NULL OR imagem_principal = '');

UPDATE municipios
SET imagem_principal = 'https://commons.wikimedia.org/wiki/Special:FilePath/Arrombada%20beach%20Luis%20Correia%20Piaui%20Brazil.jpg'
WHERE id_municipio = 121
  AND (imagem_principal IS NULL OR imagem_principal = '');

INSERT INTO midias_municipio (
    id_municipio, titulo, descricao, tipo, url_arquivo, credito, texto_alternativo,
    licenca, url_origem, categoria_visual, destaque, status_curadoria,
    criado_por, revisado_por, publicado_por
)
SELECT
    85,
    'Bandeira de Floriano',
    'Simbolo civico do municipio de Floriano.',
    'imagem',
    'https://commons.wikimedia.org/wiki/Special:FilePath/Bandeira%20de%20Floriano.jpg',
    'Prefeitura de Floriano / Wikimedia Commons',
    'Bandeira oficial do municipio de Floriano',
    'Public domain in Brazil',
    'https://commons.wikimedia.org/wiki/File:Bandeira_de_Floriano.jpg',
    'bandeira',
    0,
    'publicado',
    1,
    1,
    1
WHERE NOT EXISTS (
    SELECT 1 FROM midias_municipio WHERE id_municipio = 85 AND titulo = 'Bandeira de Floriano'
);

INSERT INTO midias_municipio (
    id_municipio, titulo, descricao, tipo, url_arquivo, credito, texto_alternativo,
    licenca, url_origem, categoria_visual, destaque, status_curadoria,
    criado_por, revisado_por, publicado_por
)
SELECT
    85,
    'Brasao de Floriano',
    'Simbolo heraldico do municipio de Floriano.',
    'imagem',
    'https://commons.wikimedia.org/wiki/Special:FilePath/Bras%C3%A3o%20de%20Floriano.jpg',
    'Wikimedia Commons',
    'Brasao do municipio de Floriano',
    'Public domain in Brazil',
    'https://commons.wikimedia.org/wiki/File:Bras%C3%A3o_de_Floriano.jpg',
    'brasao',
    0,
    'publicado',
    1,
    1,
    1
WHERE NOT EXISTS (
    SELECT 1 FROM midias_municipio WHERE id_municipio = 85 AND titulo = 'Brasao de Floriano'
);

INSERT INTO midias_municipio (
    id_municipio, titulo, descricao, tipo, url_arquivo, credito, texto_alternativo,
    licenca, url_origem, categoria_visual, destaque, status_curadoria,
    criado_por, revisado_por, publicado_por
)
SELECT
    85,
    'Catedral de Sao Pedro de Alcantara em Floriano',
    'Registro historico da reforma da catedral na praca central de Floriano.',
    'imagem',
    'https://commons.wikimedia.org/wiki/Special:FilePath/Floriano%201954.3.jpg',
    'Arquivo Pessoal / Wikimedia Commons',
    'Imagem historica da Catedral de Sao Pedro de Alcantara em Floriano',
    'Public domain in Brazil',
    'https://commons.wikimedia.org/wiki/File:Floriano_1954.3.jpg',
    'patrimonio',
    1,
    'publicado',
    1,
    1,
    1
WHERE NOT EXISTS (
    SELECT 1 FROM midias_municipio WHERE id_municipio = 85 AND titulo = 'Catedral de Sao Pedro de Alcantara em Floriano'
);

INSERT INTO midias_municipio (
    id_municipio, titulo, descricao, tipo, url_arquivo, credito, texto_alternativo,
    licenca, url_origem, categoria_visual, destaque, status_curadoria,
    criado_por, revisado_por, publicado_por
)
SELECT
    161,
    'Bandeira de Picos',
    'Simbolo civico do municipio de Picos.',
    'imagem',
    'https://commons.wikimedia.org/wiki/Special:FilePath/Bandeiradepicos.svg',
    'Pentelhojin / Wikimedia Commons',
    'Bandeira oficial do municipio de Picos',
    'Public domain',
    'https://commons.wikimedia.org/wiki/File:Bandeiradepicos.svg',
    'bandeira',
    0,
    'publicado',
    1,
    1,
    1
WHERE NOT EXISTS (
    SELECT 1 FROM midias_municipio WHERE id_municipio = 161 AND titulo = 'Bandeira de Picos'
);

INSERT INTO midias_municipio (
    id_municipio, titulo, descricao, tipo, url_arquivo, credito, texto_alternativo,
    licenca, url_origem, categoria_visual, destaque, status_curadoria,
    criado_por, revisado_por, publicado_por
)
SELECT
    161,
    'Brasao de Picos',
    'Simbolo heraldico do municipio de Picos.',
    'imagem',
    'https://commons.wikimedia.org/wiki/Special:FilePath/Escudo%20picos.svg',
    'Pentelhojin / Wikimedia Commons',
    'Brasao do municipio de Picos',
    'CC BY-SA 3.0',
    'https://commons.wikimedia.org/wiki/File:Escudo_picos.svg',
    'brasao',
    0,
    'publicado',
    1,
    1,
    1
WHERE NOT EXISTS (
    SELECT 1 FROM midias_municipio WHERE id_municipio = 161 AND titulo = 'Brasao de Picos'
);

INSERT INTO midias_municipio (
    id_municipio, titulo, descricao, tipo, url_arquivo, credito, texto_alternativo,
    licenca, url_origem, categoria_visual, destaque, status_curadoria,
    criado_por, revisado_por, publicado_por
)
SELECT
    161,
    'Catedral de Nossa Senhora dos Remedios em Picos',
    'Imagem de um dos marcos urbanos mais reconhecidos do municipio.',
    'imagem',
    'https://commons.wikimedia.org/wiki/Special:FilePath/Panor%C3%A2mica%2030.jpg',
    'Pentelhojin / Wikimedia Commons',
    'Igreja Matriz de Nossa Senhora dos Remedios em Picos',
    'CC BY-SA 4.0 and compatible free licenses',
    'https://commons.wikimedia.org/wiki/File:Panor%C3%A2mica_30.jpg',
    'patrimonio',
    1,
    'publicado',
    1,
    1,
    1
WHERE NOT EXISTS (
    SELECT 1 FROM midias_municipio WHERE id_municipio = 161 AND titulo = 'Catedral de Nossa Senhora dos Remedios em Picos'
);

INSERT INTO midias_municipio (
    id_municipio, titulo, descricao, tipo, url_arquivo, credito, texto_alternativo,
    licenca, url_origem, categoria_visual, destaque, status_curadoria,
    criado_por, revisado_por, publicado_por
)
SELECT
    165,
    'Bandeira de Piripiri',
    'Simbolo civico do municipio de Piripiri.',
    'imagem',
    'https://commons.wikimedia.org/wiki/Special:FilePath/Flag%20of%20Piripiri%2C%20Brazil.svg',
    'Vitor Fontenele / Wikimedia Commons',
    'Bandeira oficial do municipio de Piripiri',
    'Free Art License',
    'https://commons.wikimedia.org/wiki/File:Flag_of_Piripiri%2C_Brazil.svg',
    'bandeira',
    0,
    'publicado',
    1,
    1,
    1
WHERE NOT EXISTS (
    SELECT 1 FROM midias_municipio WHERE id_municipio = 165 AND titulo = 'Bandeira de Piripiri'
);

INSERT INTO midias_municipio (
    id_municipio, titulo, descricao, tipo, url_arquivo, credito, texto_alternativo,
    licenca, url_origem, categoria_visual, destaque, status_curadoria,
    criado_por, revisado_por, publicado_por
)
SELECT
    165,
    'Brasao de Piripiri',
    'Simbolo heraldico do municipio de Piripiri.',
    'imagem',
    'https://commons.wikimedia.org/wiki/Special:FilePath/Bras%C3%A3o%20de%20Piripiri.jpg',
    'Governo do Municipio de Piripiri / Wikimedia Commons',
    'Brasao do municipio de Piripiri',
    'Public domain in Brazil',
    'https://commons.wikimedia.org/wiki/File:Bras%C3%A3o_de_Piripiri.jpg',
    'brasao',
    0,
    'publicado',
    1,
    1,
    1
WHERE NOT EXISTS (
    SELECT 1 FROM midias_municipio WHERE id_municipio = 165 AND titulo = 'Brasao de Piripiri'
);

INSERT INTO midias_municipio (
    id_municipio, titulo, descricao, tipo, url_arquivo, credito, texto_alternativo,
    licenca, url_origem, categoria_visual, destaque, status_curadoria,
    criado_por, revisado_por, publicado_por
)
SELECT
    165,
    'Palacete da antiga prefeitura de Piripiri',
    'Registro do patrimonio arquitetonico ligado ao centro historico do municipio.',
    'imagem',
    'https://commons.wikimedia.org/wiki/Special:FilePath/Palacete%20da%20antiga%20prefeitura%20%28Piripiri%2C%20Piau%C3%AD%29.jpg',
    'Rr Gimenez / Wikimedia Commons',
    'Palacete historico da antiga prefeitura de Piripiri',
    'Public domain',
    'https://commons.wikimedia.org/wiki/File:Palacete_da_antiga_prefeitura_%28Piripiri%2C_Piau%C3%AD%29.jpg',
    'patrimonio',
    1,
    'publicado',
    1,
    1,
    1
WHERE NOT EXISTS (
    SELECT 1 FROM midias_municipio WHERE id_municipio = 165 AND titulo = 'Palacete da antiga prefeitura de Piripiri'
);

INSERT INTO midias_municipio (
    id_municipio, titulo, descricao, tipo, url_arquivo, credito, texto_alternativo,
    licenca, url_origem, categoria_visual, destaque, status_curadoria,
    criado_por, revisado_por, publicado_por
)
SELECT
    121,
    'Bandeira de Luis Correia',
    'Simbolo civico do municipio de Luis Correia.',
    'imagem',
    'https://commons.wikimedia.org/wiki/Special:FilePath/Bandeira%20de%20Lu%C3%ADs%20Correia.jpg',
    'Prefeitura de Luis Correia / Wikimedia Commons',
    'Bandeira oficial do municipio de Luis Correia',
    'Public domain in Brazil',
    'https://commons.wikimedia.org/wiki/File:Bandeira_de_Lu%C3%ADs_Correia.jpg',
    'bandeira',
    0,
    'publicado',
    1,
    1,
    1
WHERE NOT EXISTS (
    SELECT 1 FROM midias_municipio WHERE id_municipio = 121 AND titulo = 'Bandeira de Luis Correia'
);

INSERT INTO midias_municipio (
    id_municipio, titulo, descricao, tipo, url_arquivo, credito, texto_alternativo,
    licenca, url_origem, categoria_visual, destaque, status_curadoria,
    criado_por, revisado_por, publicado_por
)
SELECT
    121,
    'Praia da Arrombada em Luis Correia',
    'Cena litoranea do municipio de Luis Correia no litoral do Piaui.',
    'imagem',
    'https://commons.wikimedia.org/wiki/Special:FilePath/Arrombada%20beach%20Luis%20Correia%20Piaui%20Brazil.jpg',
    'LeRoc / Wikimedia Commons',
    'Praia da Arrombada em Luis Correia',
    'CC BY-SA 3.0, 2.5, 2.0, 1.0 and GFDL',
    'https://commons.wikimedia.org/wiki/File:Arrombada_beach_Luis_Correia_Piaui_Brazil.jpg',
    'paisagem',
    1,
    'publicado',
    1,
    1,
    1
WHERE NOT EXISTS (
    SELECT 1 FROM midias_municipio WHERE id_municipio = 121 AND titulo = 'Praia da Arrombada em Luis Correia'
);
