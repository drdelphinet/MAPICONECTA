<section class="page-header">
    <p class="eyebrow">Cobertura visual</p>
    <h1>Municipios publicados sem imagem suficiente</h1>
    <p>Use esta tela para identificar lacunas de imagem e montar lotes mais rapidamente para o importador do Wikimedia Commons.</p>
</section>

<section class="toolbar-card">
    <form method="get" action="<?= e(url('admin/midias/cobertura')); ?>" class="filter-grid">
        <label class="field">
            <span>Estado</span>
            <select name="estado">
                <option value="">Todos</option>
                <?php foreach (($states ?? []) as $state): ?>
                    <option value="<?= (int) $state['id_estado']; ?>" <?= ((int) ($selectedStateId ?? 0) === (int) $state['id_estado']) ? 'selected' : ''; ?>>
                        <?= e($state['estado_nome'] . ' (' . $state['estado_sigla'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <div class="toolbar-actions">
            <button type="submit" class="button">Filtrar</button>
            <a class="button button-outline" href="<?= e(url('admin/midias/descobrir-lote' . ($selectedStateId ? '?estado=' . (int) $selectedStateId : ''))); ?>">Descoberta em lote</a>
            <a class="button button-outline" href="<?= e(url('admin/midias/importar-lote-wikimedia')); ?>">Abrir importador em lote</a>
            <a class="button button-outline" href="<?= e(url('admin/midias')); ?>">Voltar para midias</a>
        </div>
    </form>

    <p class="muted-line">
        Total com lacuna visual: <strong><?= (int) ($coverageGapCount ?? 0); ?></strong>.
        A lista considera municipios publicados sem <code>imagem_principal</code> ou sem nenhuma midia publicada do tipo imagem.
    </p>
</section>

<section class="form-card">
    <h2>Lote compacto sugerido</h2>
    <p class="muted-line">Uma linha por municipio para cobertura minima. Copie, ajuste os arquivos reais do Commons e cole no importador em lote.</p>
    <textarea rows="16" readonly><?= e((string) ($suggestedBatch ?? '')); ?></textarea>
</section>

<section class="form-card">
    <h2>Lote estendido sugerido</h2>
    <p class="muted-line">Versao com paisagem, bandeira e brasao por municipio para acelerar a cobertura completa.</p>
    <textarea rows="18" readonly><?= e((string) ($extendedSuggestedBatch ?? '')); ?></textarea>
</section>

<section class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Municipio</th>
                <th>Estado</th>
                <th>Capa atual</th>
                <th>Imagens publicadas</th>
                <th>Destaques</th>
                <th>Acoes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (($coverageGaps ?? []) as $municipality): ?>
                <tr>
                    <td><?= e($municipality['nome']); ?></td>
                    <td><?= e($municipality['estado_sigla']); ?></td>
                    <td><?= trim((string) ($municipality['imagem_principal'] ?? '')) !== '' ? 'Sim' : 'Nao'; ?></td>
                    <td><?= (int) ($municipality['image_count'] ?? 0); ?></td>
                    <td><?= (int) ($municipality['highlighted_count'] ?? 0); ?></td>
                    <td>
                        <a href="<?= e(url('admin/municipios/' . $municipality['id_municipio'] . '/editar')); ?>">Editar municipio</a>
                        <span class="divider-dot">|</span>
                        <a href="<?= e(url('admin/midias/descobrir?municipio=' . urlencode((string) $municipality['slug']) . '&categoria=paisagem')); ?>">Descobrir candidatos</a>
                        <span class="divider-dot">|</span>
                        <a href="<?= e(url('admin/midias/importar-lote-wikimedia?municipio=' . urlencode((string) $municipality['slug']) . '&prefill=' . urlencode((string) ($municipality['slug'] . ' | paisagem | File:' . str_replace('-', '_', (string) $municipality['slug']) . '_vista_geral.jpg | Vista de ' . $municipality['nome'])))); ?>">Abrir no importador</a>
                        <span class="divider-dot">|</span>
                        <a href="<?= e((string) $municipality['commons_search_url']); ?>" target="_blank" rel="noopener noreferrer">Buscar imagens</a>
                        <span class="divider-dot">|</span>
                        <a href="<?= e((string) $municipality['commons_flag_search_url']); ?>" target="_blank" rel="noopener noreferrer">Bandeira</a>
                        <span class="divider-dot">|</span>
                        <a href="<?= e((string) $municipality['commons_coat_search_url']); ?>" target="_blank" rel="noopener noreferrer">Brasao</a>
                        <span class="divider-dot">|</span>
                        <a href="<?= e((string) $municipality['commons_category_url']); ?>" target="_blank" rel="noopener noreferrer">Categoria</a>
                        <span class="divider-dot">|</span>
                        <a href="<?= e(url('municipio/' . $municipality['slug'])); ?>" target="_blank" rel="noopener noreferrer">Pagina publica</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
