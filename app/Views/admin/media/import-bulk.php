<section class="page-header">
    <p class="eyebrow">Importacao em lote</p>
    <h1>Wikimedia Commons por municipio</h1>
    <p>Importe varias imagens de uma vez para um mesmo municipio, com categoria e status editoriais aplicados em bloco.</p>
</section>

<section class="toolbar-card">
    <p class="muted-line">
        Formatos aceitos por linha:
        <code>https://commons.wikimedia.org/wiki/File:...</code>,
        <code>File:Nome do arquivo.jpg | Titulo opcional</code>
        ou
        <code>categoria | File:Nome do arquivo.jpg | Titulo opcional</code>.
    </p>
    <p class="muted-line">
        Se nenhum municipio for selecionado no formulario, cada linha deve comecar com o identificador do municipio:
        <code>slug-ou-nome-ou-codigo | File:Nome.jpg</code>
        ou
        <code>slug-ou-nome-ou-codigo | categoria | File:Nome.jpg | Titulo opcional</code>.
    </p>
    <p class="muted-line">
        Linhas iniciadas com <code>#</code> sao ignoradas. Quando a categoria nao vier na linha, o sistema usa a categoria padrao do formulario.
    </p>
</section>

<section class="form-card">
    <form method="post" action="<?= e(url('admin/midias/importar-lote-wikimedia')); ?>" class="stack-form">
        <input type="hidden" name="_token" value="<?= e(\App\Core\Csrf::token()); ?>">

        <div class="form-grid">
            <label class="field">
                <span>Municipio</span>
                <select name="id_municipio">
                    <option value="">Resolver pela linha</option>
                    <?php foreach ($municipalities as $municipality): ?>
                        <option value="<?= (int) $municipality['id_municipio']; ?>" <?= ((int) ($selectedMunicipalityId ?? 0) === (int) $municipality['id_municipio']) ? 'selected' : ''; ?>>
                            <?= e($municipality['nome'] . ' (' . $municipality['estado_sigla'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="field">
                <span>Status</span>
                <select name="status_curadoria">
                    <?php foreach (['rascunho', 'enviado_revisao', 'em_correcao', 'aprovado', 'publicado'] as $status): ?>
                        <option value="<?= e($status); ?>" <?= $status === 'rascunho' ? 'selected' : ''; ?>><?= e($status); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="field">
                <span>Categoria padrao</span>
                <select name="categoria_visual">
                    <option value="">Sem categoria</option>
                    <?php foreach (($visualCategories ?? []) as $category): ?>
                        <option value="<?= e($category); ?>"><?= e(ucfirst($category)); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="field">
                <span>Destaque</span>
                <select name="destaque">
                    <option value="0" selected>Normal</option>
                    <option value="1">Todas destacadas</option>
                </select>
            </label>

            <label class="field">
                <span>Imagem principal</span>
                <select name="preencher_imagem_principal">
                    <option value="1" selected>Preencher quando estiver vazia</option>
                    <option value="0">Nao alterar capa do municipio</option>
                </select>
            </label>

            <label class="field field-full">
                <span>Linhas para importacao</span>
                <textarea name="wikimedia_sources" rows="12" required placeholder="picos | patrimonio | https://commons.wikimedia.org/wiki/File:Panor%C3%A2mica_30.jpg | Catedral de Nossa Senhora dos Remedios em Picos&#10;picos | bandeira | File:Bandeira_de_Picos.svg | Bandeira de Picos&#10;ou, com municipio ja selecionado acima:&#10;brasao | File:Escudo_picos.svg | Brasao de Picos"><?= e((string) ($prefillSources ?? '')); ?></textarea>
            </label>
        </div>

        <div class="toolbar-actions">
            <button type="submit" class="button">Importar lote</button>
            <a class="button button-outline" href="<?= e(url('admin/midias')); ?>">Voltar</a>
        </div>
    </form>
</section>
