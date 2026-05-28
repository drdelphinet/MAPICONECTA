<section class="page-header">
    <p class="eyebrow">Quizzes e gamificacao</p>
    <h1><?= $question ? 'Editar pergunta' : 'Nova pergunta'; ?></h1>
    <p><strong><?= e($quiz['titulo']); ?></strong> • <?= e($quiz['municipio_nome']); ?></p>
</section>

<section class="form-card">
    <form method="post" action="<?= e($formAction); ?>" class="stack-form">
        <input type="hidden" name="_token" value="<?= e(\App\Core\Csrf::token()); ?>">

        <div class="form-grid">
            <label class="field field-full">
                <span>Pergunta</span>
                <textarea name="pergunta" rows="4" required><?= e((string) ($question['pergunta'] ?? '')); ?></textarea>
            </label>

            <label class="field">
                <span>Alternativa A</span>
                <input type="text" name="alternativa_a" value="<?= e((string) ($question['alternativa_a'] ?? '')); ?>" required>
            </label>
            <label class="field">
                <span>Alternativa B</span>
                <input type="text" name="alternativa_b" value="<?= e((string) ($question['alternativa_b'] ?? '')); ?>" required>
            </label>
            <label class="field">
                <span>Alternativa C</span>
                <input type="text" name="alternativa_c" value="<?= e((string) ($question['alternativa_c'] ?? '')); ?>" required>
            </label>
            <label class="field">
                <span>Alternativa D</span>
                <input type="text" name="alternativa_d" value="<?= e((string) ($question['alternativa_d'] ?? '')); ?>" required>
            </label>

            <label class="field">
                <span>Resposta correta</span>
                <select name="resposta_correta">
                    <?php foreach (['a', 'b', 'c', 'd'] as $letter): ?>
                        <option value="<?= e($letter); ?>" <?= (($question['resposta_correta'] ?? 'a') === $letter) ? 'selected' : ''; ?>><?= e(strtoupper($letter)); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="field">
                <span>Pontos</span>
                <input type="number" name="pontos" min="1" value="<?= e((string) ($question['pontos'] ?? 10)); ?>" required>
            </label>

            <label class="field">
                <span>Ordem</span>
                <input type="number" name="ordem" min="1" value="<?= e((string) ($question['ordem'] ?? 1)); ?>" required>
            </label>

            <label class="field">
                <span>Status</span>
                <select name="status_curadoria">
                    <?php foreach (['rascunho', 'enviado_revisao', 'em_correcao', 'aprovado', 'publicado', 'reprovado', 'arquivado'] as $status): ?>
                        <option value="<?= e($status); ?>" <?= (($question['status_curadoria'] ?? 'rascunho') === $status) ? 'selected' : ''; ?>><?= e($status); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="field field-full">
                <span>Explicacao</span>
                <textarea name="explicacao" rows="4"><?= e((string) ($question['explicacao'] ?? '')); ?></textarea>
            </label>
        </div>

        <div class="toolbar-actions">
            <button type="submit" class="button">Salvar</button>
            <a class="button button-outline" href="<?= e(url('admin/quizzes/' . $quiz['id_quiz'] . '/perguntas')); ?>">Voltar</a>
        </div>
    </form>
</section>
