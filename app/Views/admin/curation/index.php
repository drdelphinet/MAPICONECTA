<section class="page-header">
    <p class="eyebrow">Fluxo editorial</p>
    <h1>Curadoria</h1>
    <p>Acompanhe o funil de revisao, veja o que esta pendente e abra os conteudos para aprovar, corrigir, reprovar ou publicar.</p>
</section>

<section class="stats-grid">
    <?php foreach (['rascunho', 'enviado_revisao', 'em_correcao', 'aprovado', 'publicado', 'reprovado', 'arquivado'] as $statusItem): ?>
        <article class="stat-card">
            <span><?= e($statusItem); ?></span>
            <strong><?= (int) ($summary[$statusItem] ?? 0); ?></strong>
        </article>
    <?php endforeach; ?>
</section>

<section class="toolbar-card">
    <form method="get" action="<?= e(url('admin/curadoria')); ?>" class="filter-grid">
        <label class="field">
            <span>Status da fila</span>
            <select name="status">
                <?php foreach (['enviado_revisao', 'em_correcao', 'aprovado', 'reprovado', 'arquivado', 'publicado', 'rascunho'] as $statusItem): ?>
                    <option value="<?= e($statusItem); ?>" <?= $status === $statusItem ? 'selected' : ''; ?>><?= e($statusItem); ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <div class="toolbar-actions align-end">
            <button type="submit" class="button">Filtrar fila</button>
        </div>
    </form>
</section>

<section class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Municipio</th>
                <th>Estado</th>
                <th>Curadoria</th>
                <th>Publicacao</th>
                <th>Atualizado em</th>
                <th>Acoes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= e($item['nome']); ?></td>
                    <td><?= e($item['estado_sigla']); ?></td>
                    <td><span class="badge"><?= e($item['status_curadoria']); ?></span></td>
                    <td><span class="badge"><?= e($item['status_publicacao']); ?></span></td>
                    <td><?= e((string) $item['atualizado_em']); ?></td>
                    <td><a href="<?= e(url('admin/municipios/' . $item['id_municipio'] . '/editar')); ?>">Abrir analise</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
