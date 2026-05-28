<section class="page-header">
    <p class="eyebrow">Galeria</p>
    <h1><?= $mediaItem ? 'Editar midia' : 'Nova midia'; ?></h1>
    <p>Cadastre arquivos de apoio com tipo, credito, texto alternativo e destaque editorial para mostrar paisagens, simbolos, patrimonio e cenas do municipio.</p>
</section>

<section class="form-card">
    <form method="post" action="<?= e($formAction); ?>" class="stack-form">
        <input type="hidden" name="_token" value="<?= e(\App\Core\Csrf::token()); ?>">

        <div class="form-grid">
            <label class="field">
                <span>Municipio</span>
                <select name="id_municipio" required>
                    <option value="">Selecione</option>
                    <?php foreach ($municipalities as $municipality): ?>
                        <option value="<?= (int) $municipality['id_municipio']; ?>" <?= ((int) ($mediaItem['id_municipio'] ?? 0) === (int) $municipality['id_municipio']) ? 'selected' : ''; ?>>
                            <?= e($municipality['nome'] . ' (' . $municipality['estado_sigla'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="field">
                <span>Status</span>
                <select name="status_curadoria">
                    <?php foreach (['rascunho', 'enviado_revisao', 'em_correcao', 'aprovado', 'publicado', 'reprovado', 'arquivado'] as $status): ?>
                        <option value="<?= e($status); ?>" <?= (($mediaItem['status_curadoria'] ?? 'rascunho') === $status) ? 'selected' : ''; ?>><?= e($status); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="field field-full">
                <span>Titulo</span>
                <input type="text" name="titulo" value="<?= e((string) ($mediaItem['titulo'] ?? '')); ?>" required>
            </label>

            <label class="field">
                <span>Tipo</span>
                <select name="tipo">
                    <?php foreach (['imagem', 'video', 'audio', 'documento'] as $type): ?>
                        <option value="<?= e($type); ?>" <?= (($mediaItem['tipo'] ?? 'imagem') === $type) ? 'selected' : ''; ?>><?= e($type); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="field">
                <span>Categoria visual</span>
                <select name="categoria_visual">
                    <option value="">Sem categoria</option>
                    <?php foreach (($visualCategories ?? []) as $category): ?>
                        <option value="<?= e($category); ?>" <?= (($mediaItem['categoria_visual'] ?? '') === $category) ? 'selected' : ''; ?>>
                            <?= e(ucfirst($category)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="field">
                <span>Destaque</span>
                <select name="destaque">
                    <option value="0" <?= ((int) ($mediaItem['destaque'] ?? 0) === 0) ? 'selected' : ''; ?>>Normal</option>
                    <option value="1" <?= ((int) ($mediaItem['destaque'] ?? 0) === 1) ? 'selected' : ''; ?>>Destacada</option>
                </select>
            </label>

            <label class="field field-full">
                <span>URL do arquivo</span>
                <input type="text" name="url_arquivo" value="<?= e((string) ($mediaItem['url_arquivo'] ?? '')); ?>" required placeholder="https://...">
            </label>

            <label class="field field-full">
                <span>Descricao</span>
                <textarea name="descricao" rows="4"><?= e((string) ($mediaItem['descricao'] ?? '')); ?></textarea>
            </label>

            <label class="field">
                <span>Credito</span>
                <input type="text" name="credito" value="<?= e((string) ($mediaItem['credito'] ?? '')); ?>">
            </label>

            <label class="field">
                <span>Texto alternativo</span>
                <input type="text" name="texto_alternativo" value="<?= e((string) ($mediaItem['texto_alternativo'] ?? '')); ?>" placeholder="Ex.: Vista da praca central ao entardecer">
            </label>

            <label class="field">
                <span>Licenca</span>
                <input type="text" name="licenca" value="<?= e((string) ($mediaItem['licenca'] ?? '')); ?>" placeholder="Ex.: CC BY-SA 4.0">
            </label>

            <label class="field">
                <span>URL de origem</span>
                <input type="text" name="url_origem" value="<?= e((string) ($mediaItem['url_origem'] ?? '')); ?>" placeholder="https://commons.wikimedia.org/wiki/File:...">
            </label>
        </div>

        <div class="toolbar-actions">
            <button type="submit" class="button">Salvar</button>
            <a class="button button-outline" href="<?= e(url('admin/midias')); ?>">Voltar</a>
        </div>
    </form>
</section>
