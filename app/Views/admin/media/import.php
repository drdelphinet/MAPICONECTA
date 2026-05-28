<section class="page-header">
    <p class="eyebrow">Importacao assistida</p>
    <h1>Wikimedia Commons</h1>
    <p>Cole a URL da pagina do arquivo no Wikimedia Commons ou o titulo do arquivo para puxar imagem, credito, licenca e origem com menos trabalho manual.</p>
</section>

<section class="toolbar-card">
    <p class="muted-line">
        Formatos recomendados:
        <code>https://commons.wikimedia.org/wiki/File:...</code>
        ou
        <code>File:Nome do arquivo.jpg</code>.
    </p>
    <p class="muted-line">
        O sistema tenta preencher automaticamente URL do arquivo, credito, descricao, texto alternativo, licenca e link de origem.
    </p>
</section>

<section class="form-card">
    <form method="post" action="<?= e(url('admin/midias/importar-wikimedia')); ?>" class="stack-form">
        <input type="hidden" name="_token" value="<?= e(\App\Core\Csrf::token()); ?>">

        <div class="form-grid">
            <label class="field field-full">
                <span>Arquivo do Wikimedia Commons</span>
                <input type="text" name="wikimedia_source" required placeholder="https://commons.wikimedia.org/wiki/File:..." value="<?= e((string) ($prefillSource ?? '')); ?>">
            </label>

            <label class="field">
                <span>Municipio</span>
                <select name="id_municipio" required>
                    <option value="">Selecione</option>
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
                <span>Categoria visual</span>
                <select name="categoria_visual">
                    <option value="">Sem categoria</option>
                    <?php foreach (($visualCategories ?? []) as $category): ?>
                        <option value="<?= e($category); ?>" <?= (($prefillCategory ?? '') === $category) ? 'selected' : ''; ?>><?= e(ucfirst($category)); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="field">
                <span>Destaque</span>
                <select name="destaque">
                    <option value="0" selected>Normal</option>
                    <option value="1">Destacada</option>
                </select>
            </label>

            <label class="field field-full">
                <span>Titulo opcional</span>
                <input type="text" name="titulo" placeholder="Deixe vazio para usar o titulo do arquivo" value="<?= e((string) ($prefillTitle ?? '')); ?>">
            </label>

            <label class="field field-full">
                <span>Descricao opcional</span>
                <textarea name="descricao" rows="3" placeholder="Deixe vazio para usar a descricao retornada pelo Wikimedia Commons"></textarea>
            </label>

            <label class="field">
                <span>Credito opcional</span>
                <input type="text" name="credito" placeholder="Deixe vazio para usar o credito retornado">
            </label>

            <label class="field">
                <span>Texto alternativo opcional</span>
                <input type="text" name="texto_alternativo" placeholder="Deixe vazio para usar a descricao como apoio">
            </label>

            <label class="field">
                <span>Licenca opcional</span>
                <input type="text" name="licenca" placeholder="Deixe vazio para usar a licenca retornada">
            </label>

            <label class="field">
                <span>URL de origem opcional</span>
                <input type="text" name="url_origem" placeholder="Deixe vazio para usar a pagina do Wikimedia Commons">
            </label>
        </div>

        <div class="toolbar-actions">
            <button type="submit" class="button">Importar midia</button>
            <a class="button button-outline" href="<?= e(url('admin/midias')); ?>">Voltar</a>
        </div>
    </form>
</section>
