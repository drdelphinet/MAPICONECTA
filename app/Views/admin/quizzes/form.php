<section class="page-header">
    <p class="eyebrow">Quizzes e gamificacao</p>
    <h1><?= $quiz ? 'Editar quiz' : 'Novo quiz'; ?></h1>
    <p>Cadastro do quiz vinculado a um municipio.</p>
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
                        <option value="<?= (int) $municipality['id_municipio']; ?>" <?= ((int) ($quiz['id_municipio'] ?? 0) === (int) $municipality['id_municipio']) ? 'selected' : ''; ?>>
                            <?= e($municipality['nome'] . ' (' . $municipality['estado_sigla'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="field">
                <span>Status</span>
                <select name="status_curadoria">
                    <?php foreach (['rascunho', 'enviado_revisao', 'em_correcao', 'aprovado', 'publicado', 'reprovado', 'arquivado'] as $status): ?>
                        <option value="<?= e($status); ?>" <?= (($quiz['status_curadoria'] ?? 'rascunho') === $status) ? 'selected' : ''; ?>><?= e($status); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="field field-full">
                <span>Titulo</span>
                <input type="text" name="titulo" value="<?= e((string) ($quiz['titulo'] ?? '')); ?>" required>
            </label>

            <label class="field field-full">
                <span>Descricao</span>
                <textarea name="descricao" rows="4"><?= e((string) ($quiz['descricao'] ?? '')); ?></textarea>
            </label>
        </div>

        <div class="toolbar-actions">
            <button type="submit" class="button">Salvar</button>
            <?php if ($quiz): ?>
                <a class="button button-outline" href="<?= e(url('admin/quizzes/' . $quiz['id_quiz'] . '/perguntas')); ?>">Perguntas</a>
            <?php endif; ?>
            <a class="button button-outline" href="<?= e(url('admin/quizzes')); ?>">Voltar</a>
        </div>
    </form>
</section>
