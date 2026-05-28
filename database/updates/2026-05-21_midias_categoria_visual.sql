ALTER TABLE midias_municipio
    ADD COLUMN IF NOT EXISTS categoria_visual VARCHAR(40) NULL AFTER url_origem;

UPDATE midias_municipio
SET categoria_visual = 'paisagem'
WHERE categoria_visual IS NULL
  AND tipo = 'imagem'
  AND (
        titulo LIKE '%vista%'
        OR titulo LIKE '%paisagem%'
        OR titulo LIKE '%urbana%'
      );
