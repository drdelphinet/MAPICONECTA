<section class="page-header">
    <p class="eyebrow">Descoberta assistida</p>
    <h1>Candidatos do Wikimedia Commons</h1>
    <p>Escolha um municipio, defina a categoria visual e veja arquivos candidatos antes de importar.</p>
</section>

<section class="form-card">
    <form method="get" action="<?= e(url('admin/midias/descobrir')); ?>" class="stack-form">
        <div class="form-grid">
            <label class="field">
                <span>Municipio</span>
                <select name="municipio" required>
                    <option value="">Selecione</option>
                    <?php foreach ($municipalities as $item): ?>
                        <option value="<?= e($item['slug']); ?>" <?= ((int) ($selectedMunicipalityId ?? 0) === (int) $item['id_municipio']) ? 'selected' : ''; ?>>
                            <?= e($item['nome'] . ' (' . $item['estado_sigla'] . ')'); ?>
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
        </div>

        <div class="toolbar-actions">
            <button type="submit" class="button">Buscar candidatos</button>
            <a class="button button-outline" href="<?= e(url('admin/midias/cobertura')); ?>">Voltar para cobertura</a>
        </div>
    </form>
</section>

<?php if (($municipality ?? null) !== null): ?>
    <section class="toolbar-card">
        <p class="muted-line">Consulta usada: <code><?= e((string) ($query ?? '')); ?></code></p>
        <p class="muted-line">A confianca combina municipio, estado e categoria para ajudar a priorizar importacoes mais seguras.</p>
    </section>
<?php endif; ?>

<?php if (($results ?? []) !== []): ?>
    <section class="cards-grid">
        <?php foreach ($results as $result): ?>
            <article class="panel-card municipality-card">
                <?php if (!empty($result['thumbnail_url'])): ?>
                    <img src="<?= e((string) $result['thumbnail_url']); ?>" alt="<?= e((string) $result['title']); ?>" style="max-width: 100%; border-radius: 1rem; margin-bottom: 1rem;">
                <?php endif; ?>
                <p class="eyebrow"><?= e((string) ($selectedCategory ?? 'imagem')); ?></p>
                <h2><?= e((string) $result['title']); ?></h2>
                <p class="muted-line">Confianca: <?= e((string) ($result['confidence_label'] ?? 'n/d')); ?> (<?= (int) ($result['confidence_score'] ?? 0); ?>/100)</p>
                <?php if (!empty($result['already_imported'])): ?>
                    <p class="muted-line">Ja importada para este municipio. Status atual: <?= e((string) ($result['existing_status'] ?? 'n/d')); ?></p>
                <?php endif; ?>
                <p><?= e((string) (($result['description'] ?? '') !== '' ? $result['description'] : 'Sem descricao resumida retornada pelo Wikimedia Commons.')); ?></p>
                <p class="muted-line">Licenca: <?= e((string) (($result['license'] ?? '') !== '' ? $result['license'] : '-')); ?></p>
                <div class="toolbar-actions">
                    <?php if (!empty($result['already_imported']) && !empty($result['existing_media_id'])): ?>
                        <a class="button" href="<?= e(url('admin/midias/' . (int) $result['existing_media_id'] . '/editar')); ?>">Abrir midia cadastrada</a>
                    <?php else: ?>
                        <a class="button" href="<?= e(url('admin/midias/importar-wikimedia?municipio=' . urlencode((string) ($municipality['slug'] ?? '')) . '&categoria=' . urlencode((string) ($selectedCategory ?? '')) . '&source=' . urlencode((string) ($result['canonical_title'] ?? '')) . '&titulo=' . urlencode((string) ($result['title'] ?? '')))); ?>">Importar esta</a>
                    <?php endif; ?>
                    <a class="button button-outline" href="<?= e((string) $result['source_url']); ?>" target="_blank" rel="noopener noreferrer">Abrir no Commons</a>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
<?php elseif (($municipality ?? null) !== null): ?>
    <section class="toolbar-card">
        <p class="muted-line">Nenhum candidato foi encontrado para essa busca. Tente outra categoria ou refine o municipio manualmente no Commons.</p>
    </section>
<?php endif; ?>
