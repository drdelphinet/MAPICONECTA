<section class="page-header">
    <p class="eyebrow">Descoberta em lote</p>
    <h1>Candidatos por estado no Wikimedia Commons</h1>
    <p>Veja rapidamente alguns candidatos por município com lacuna visual e avance para importação individual quando encontrar um bom arquivo.</p>
    <p class="muted-line">A selecao automatica marca apenas municipios com melhor candidato em confianca Boa ou Alta (70/100 ou mais).</p>
</section>

<section class="form-card">
    <form method="get" action="<?= e(url('admin/midias/descobrir-lote')); ?>" class="stack-form">
        <div class="form-grid">
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

            <label class="field">
                <span>Categoria</span>
                <select name="categoria">
                    <?php foreach (['paisagem', 'patrimonio', 'atrativo', 'bandeira', 'brasao'] as $category): ?>
                        <option value="<?= e($category); ?>" <?= (($selectedCategory ?? 'paisagem') === $category) ? 'selected' : ''; ?>><?= e(ucfirst($category)); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="field">
                <span>Municipios</span>
                <select name="limite">
                    <?php foreach ([5, 8, 12, 20] as $option): ?>
                        <option value="<?= $option; ?>" <?= ((int) ($selectedLimit ?? 8) === $option) ? 'selected' : ''; ?>><?= $option; ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>

        <div class="toolbar-actions">
            <button type="submit" class="button">Gerar descoberta em lote</button>
            <a class="button button-outline" href="<?= e(url('admin/midias/cobertura')); ?>">Voltar para cobertura</a>
        </div>
    </form>
</section>

<?php if (($items ?? []) !== []): ?>
    <section class="form-card">
        <form method="post" action="<?= e(url('admin/midias/descobrir-lote/importar')); ?>" class="stack-form">
            <input type="hidden" name="_token" value="<?= e(\App\Core\Csrf::token()); ?>">
            <input type="hidden" name="estado" value="<?= (int) ($selectedStateId ?? 0); ?>">
            <input type="hidden" name="categoria" value="<?= e((string) ($selectedCategory ?? 'paisagem')); ?>">
            <input type="hidden" name="limite" value="<?= (int) ($selectedLimit ?? 8); ?>">

            <div class="form-grid">
                <label class="field">
                    <span>Status</span>
                    <select name="status_curadoria">
                        <?php foreach (['rascunho', 'enviado_revisao', 'em_correcao', 'aprovado', 'publicado'] as $status): ?>
                            <option value="<?= e($status); ?>" <?= $status === 'rascunho' ? 'selected' : ''; ?>><?= e($status); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label class="field">
                    <span>Destaque</span>
                    <select name="destaque">
                        <option value="0" selected>Normal</option>
                        <option value="1">Destacar importadas</option>
                    </select>
                </label>

                <label class="field">
                    <span>Imagem principal</span>
                    <select name="preencher_imagem_principal">
                        <option value="1" selected>Preencher quando estiver vazia</option>
                        <option value="0">Nao alterar capa</option>
                    </select>
                </label>
            </div>

            <div class="toolbar-actions">
                <button type="submit" class="button">Importar primeiro candidato valido de cada municipio</button>
            </div>

            <div class="table-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Importar</th>
                            <th>Municipio</th>
                            <th>Estado</th>
                            <th>Candidatos</th>
                            <th>Melhor confianca</th>
                            <th>Consulta</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($items ?? []) as $item): ?>
                            <?php $municipality = $item['municipality']; ?>
                            <tr>
                                <td>
                                    <input
                                        type="checkbox"
                                        name="municipios[]"
                                        value="<?= (int) $municipality['id_municipio']; ?>"
                                        <?= !empty($item['auto_selectable']) ? 'checked' : ''; ?>
                                        <?= empty($item['candidates']) ? 'disabled' : ''; ?>
                                    >
                                </td>
                                <td><?= e($municipality['nome']); ?></td>
                                <td><?= e($municipality['estado_sigla']); ?></td>
                                <td><?= count($item['candidates'] ?? []); ?></td>
                                <td><?= e(($item['best_score'] ?? 0) > 0 ? (($item['best_score'] ?? 0) . '/100') : '-'); ?></td>
                                <td><code><?= e((string) $item['query']); ?></code></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </section>
<?php endif; ?>

<?php foreach (($items ?? []) as $item): ?>
    <?php $municipality = $item['municipality']; ?>
    <section class="toolbar-card">
        <p><strong><?= e($municipality['nome']); ?> (<?= e($municipality['estado_sigla']); ?>)</strong></p>
        <p class="muted-line">Consulta: <code><?= e((string) $item['query']); ?></code></p>
        <div class="toolbar-actions">
            <a class="button button-outline" href="<?= e(url('admin/midias/descobrir?municipio=' . urlencode((string) $municipality['slug']) . '&categoria=' . urlencode((string) ($selectedCategory ?? 'paisagem')))); ?>">Abrir descoberta detalhada</a>
            <a class="button button-outline" href="<?= e(url('admin/midias/importar-lote-wikimedia?municipio=' . urlencode((string) $municipality['slug']))); ?>">Importar lote deste municipio</a>
            <a class="button button-outline" href="<?= e(url('municipio/' . $municipality['slug'])); ?>" target="_blank" rel="noopener noreferrer">Pagina publica</a>
        </div>
    </section>

    <?php if (!empty($item['error'])): ?>
        <section class="toolbar-card">
            <p class="muted-line"><?= e((string) $item['error']); ?></p>
        </section>
    <?php elseif (($item['candidates'] ?? []) !== []): ?>
        <section class="cards-grid">
            <?php foreach ($item['candidates'] as $candidate): ?>
                <article class="panel-card municipality-card">
                    <?php if (!empty($candidate['thumbnail_url'])): ?>
                        <img src="<?= e((string) $candidate['thumbnail_url']); ?>" alt="<?= e((string) $candidate['title']); ?>" style="max-width: 100%; border-radius: 1rem; margin-bottom: 1rem;">
                    <?php endif; ?>
                    <p class="eyebrow"><?= e((string) ($selectedCategory ?? 'imagem')); ?></p>
                    <h2><?= e((string) $candidate['title']); ?></h2>
                    <p class="muted-line">Confianca: <?= e((string) ($candidate['confidence_label'] ?? 'n/d')); ?> (<?= (int) ($candidate['confidence_score'] ?? 0); ?>/100)</p>
                    <?php if (!empty($candidate['already_imported'])): ?>
                        <p class="muted-line">Ja importada. Status: <?= e((string) ($candidate['existing_status'] ?? 'n/d')); ?></p>
                    <?php endif; ?>
                    <p><?= e((string) (($candidate['description'] ?? '') !== '' ? $candidate['description'] : 'Sem resumo retornado pelo Commons.')); ?></p>
                    <div class="toolbar-actions">
                        <?php if (!empty($candidate['already_imported']) && !empty($candidate['existing_media_id'])): ?>
                            <a class="button" href="<?= e(url('admin/midias/' . (int) $candidate['existing_media_id'] . '/editar')); ?>">Abrir midia cadastrada</a>
                        <?php else: ?>
                            <a class="button" href="<?= e(url('admin/midias/importar-wikimedia?municipio=' . urlencode((string) $municipality['slug']) . '&categoria=' . urlencode((string) ($selectedCategory ?? 'paisagem')) . '&source=' . urlencode((string) ($candidate['canonical_title'] ?? '')) . '&titulo=' . urlencode((string) ($candidate['title'] ?? '')))); ?>">Importar esta</a>
                        <?php endif; ?>
                        <a class="button button-outline" href="<?= e((string) $candidate['source_url']); ?>" target="_blank" rel="noopener noreferrer">Abrir no Commons</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php else: ?>
        <section class="toolbar-card">
            <p class="muted-line">Nenhum candidato retornado para este municipio nessa categoria.</p>
        </section>
    <?php endif; ?>
<?php endforeach; ?>
