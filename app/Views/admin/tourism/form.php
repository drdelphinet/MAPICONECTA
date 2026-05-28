<section class="page-header">
    <p class="eyebrow">Turismo</p>
    <h1><?= $point ? 'Editar ponto turistico' : 'Novo ponto turistico'; ?></h1>
    <p>Organize atrativos locais com descricao, endereco, contato, imagem e status de curadoria.</p>
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
                        <option value="<?= (int) $municipality['id_municipio']; ?>" <?= ((int) ($point['id_municipio'] ?? 0) === (int) $municipality['id_municipio']) ? 'selected' : ''; ?>>
                            <?= e($municipality['nome'] . ' (' . $municipality['estado_sigla'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="field">
                <span>Status</span>
                <select name="status_curadoria">
                    <?php foreach (['rascunho', 'enviado_revisao', 'em_correcao', 'aprovado', 'publicado', 'reprovado', 'arquivado'] as $status): ?>
                        <option value="<?= e($status); ?>" <?= (($point['status_curadoria'] ?? 'rascunho') === $status) ? 'selected' : ''; ?>><?= e($status); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="field field-full">
                <span>Nome</span>
                <input type="text" name="nome" value="<?= e((string) ($point['nome'] ?? '')); ?>" required>
            </label>

            <label class="field">
                <span>Categoria</span>
                <input type="text" name="categoria" value="<?= e((string) ($point['categoria'] ?? '')); ?>" placeholder="Ex.: Museu, praca, praia, patrimonio">
            </label>

            <label class="field">
                <span>Valor de entrada</span>
                <input type="text" name="valor_entrada" value="<?= e((string) ($point['valor_entrada'] ?? '')); ?>" placeholder="Ex.: Gratuito">
            </label>

            <label class="field field-full">
                <span>Descricao</span>
                <textarea name="descricao" rows="4"><?= e((string) ($point['descricao'] ?? '')); ?></textarea>
            </label>

            <label class="field field-full">
                <span>Endereco</span>
                <input type="text" name="endereco" value="<?= e((string) ($point['endereco'] ?? '')); ?>">
            </label>

            <label class="field">
                <span>Latitude</span>
                <input type="number" step="0.0000001" name="latitude" value="<?= e((string) ($point['latitude'] ?? '')); ?>">
            </label>

            <label class="field">
                <span>Longitude</span>
                <input type="number" step="0.0000001" name="longitude" value="<?= e((string) ($point['longitude'] ?? '')); ?>">
            </label>

            <label class="field">
                <span>Horario de funcionamento</span>
                <input type="text" name="horario_funcionamento" value="<?= e((string) ($point['horario_funcionamento'] ?? '')); ?>">
            </label>

            <label class="field">
                <span>Contato</span>
                <input type="text" name="contato" value="<?= e((string) ($point['contato'] ?? '')); ?>">
            </label>

            <label class="field">
                <span>Imagem</span>
                <input type="text" name="imagem" value="<?= e((string) ($point['imagem'] ?? '')); ?>" placeholder="https://...">
            </label>

            <label class="field">
                <span>Fonte</span>
                <input type="text" name="fonte" value="<?= e((string) ($point['fonte'] ?? '')); ?>">
            </label>
        </div>

        <div class="toolbar-actions">
            <button type="submit" class="button">Salvar</button>
            <a class="button button-outline" href="<?= e(url('admin/turismo')); ?>">Voltar</a>
        </div>
    </form>
</section>
