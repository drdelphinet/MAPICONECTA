ALTER TABLE midias_municipio
    ADD COLUMN IF NOT EXISTS licenca VARCHAR(120) NULL AFTER texto_alternativo,
    ADD COLUMN IF NOT EXISTS url_origem VARCHAR(255) NULL AFTER licenca;
