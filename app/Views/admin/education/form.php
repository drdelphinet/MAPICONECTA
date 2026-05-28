<section class="page-header">
    <p class="eyebrow">Area pedagogica</p>
    <h1><?= $activity ? 'Editar atividade pedagogica' : 'Nova atividade pedagogica'; ?></h1>
    <p>Cadastre materiais educacionais vinculados a um municipio publicado.</p>
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
                        <option value="<?= (int) $municipality['id_municipio']; ?>" <?= ((int) ($activity['id_municipio'] ?? 0) === (int) $municipality['id_municipio']) ? 'selected' : ''; ?>>
                            <?= e($municipality['nome'] . ' (' . $municipality['estado_sigla'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="field">
                <span>Status</span>
                <select name="status_curadoria">
                    <?php foreach (['rascunho', 'enviado_revisao', 'em_correcao', 'aprovado', 'publicado', 'reprovado', 'arquivado'] as $status): ?>
                        <option value="<?= e($status); ?>" <?= (($activity['status_curadoria'] ?? 'rascunho') === $status) ? 'selected' : ''; ?>><?= e($status); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="field field-full">
                <span>Titulo</span>
                <input type="text" name="titulo" value="<?= e((string) ($activity['titulo'] ?? '')); ?>" required>
            </label>

            <label class="field">
                <span>Disciplina</span>
                <input type="text" name="disciplina" value="<?= e((string) ($activity['disciplina'] ?? '')); ?>" required>
            </label>

            <label class="field">
                <span>Ano escolar</span>
                <input type="text" name="ano_escolar" value="<?= e((string) ($activity['ano_escolar'] ?? '')); ?>" placeholder="Ex.: 8o e 9o ano">
            </label>

            <label class="field field-full">
                <span>Descricao</span>
                <textarea name="descricao" rows="3"><?= e((string) ($activity['descricao'] ?? '')); ?></textarea>
            </label>

            <label class="field field-full">
                <span>Objetivo</span>
                <textarea name="objetivo" rows="3"><?= e((string) ($activity['objetivo'] ?? '')); ?></textarea>
            </label>

            <label class="field field-full">
                <span>Metodologia</span>
                <textarea name="metodologia" rows="3"><?= e((string) ($activity['metodologia'] ?? '')); ?></textarea>
            </label>

            <label class="field field-full">
                <span>Recursos necessarios</span>
                <textarea name="recursos_necessarios" rows="3"><?= e((string) ($activity['recursos_necessarios'] ?? '')); ?></textarea>
            </label>

            <label class="field field-full">
                <span>Desenvolvimento</span>
                <textarea name="desenvolvimento" rows="5"><?= e((string) ($activity['desenvolvimento'] ?? '')); ?></textarea>
            </label>

            <label class="field field-full">
                <span>Avaliacao</span>
                <textarea name="avaliacao" rows="3"><?= e((string) ($activity['avaliacao'] ?? '')); ?></textarea>
            </label>

            <label class="field field-full">
                <span>Arquivo PDF</span>
                <input type="text" name="arquivo_pdf" value="<?= e((string) ($activity['arquivo_pdf'] ?? '')); ?>" placeholder="URL ou caminho do material">
            </label>
        </div>

        <div class="toolbar-actions">
            <button type="submit" class="button">Salvar</button>
            <a class="button button-outline" href="<?= e(url('admin/educacao')); ?>">Voltar</a>
        </div>
    </form>
</section>
