<?php
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => url('')],
    ['label' => 'Busca'],
];
require app_path('app/Views/partials/breadcrumbs.php');
?>

<section class="container page-header">
    <p class="eyebrow">Busca publica</p>
    <h1>Encontre municipios, atrativos, atividades e midias</h1>
    <p>Use uma palavra-chave para atravessar o acervo territorial e descobrir rapidamente o que ja esta publicado.</p>
</section>

<section class="container toolbar-card">
    <form method="get" action="<?= e(url('busca')); ?>" class="search-page-form">
        <label class="field field-full">
            <span>O que voce quer encontrar?</span>
            <input type="text" name="q" value="<?= e($search); ?>" placeholder="Ex.: Oeiras, arqueologia, feira, lagoa, identidade local">
        </label>
        <label class="field">
            <span>Tipo de conteudo</span>
            <select name="tipo">
                <option value="todos" <?= (($selectedType ?? 'todos') === 'todos') ? 'selected' : ''; ?>>Tudo</option>
                <option value="municipios" <?= (($selectedType ?? 'todos') === 'municipios') ? 'selected' : ''; ?>>Municipios</option>
                <option value="turismo" <?= (($selectedType ?? 'todos') === 'turismo') ? 'selected' : ''; ?>>Turismo</option>
                <option value="educacao" <?= (($selectedType ?? 'todos') === 'educacao') ? 'selected' : ''; ?>>MAPI Educacao</option>
                <option value="midias" <?= (($selectedType ?? 'todos') === 'midias') ? 'selected' : ''; ?>>Midias</option>
            </select>
        </label>
        <div class="toolbar-actions align-end">
            <button type="submit" class="button">Buscar</button>
            <a class="button button-outline" href="<?= e(url('busca')); ?>">Limpar</a>
        </div>
    </form>
</section>

<section class="container municipalities-overview">
    <article class="panel-card municipalities-overview-card">
        <p class="eyebrow">Panorama</p>
        <h2><?= (int) ($visibleResultCount ?? 0); ?> resultado(s)</h2>
        <p>
            <?php if (($search ?? '') === ''): ?>
                Digite um termo para iniciar a busca no acervo publicado.
            <?php else: ?>
                Busca atual: <strong><?= e($search); ?></strong>
                <?php if (($selectedType ?? 'todos') !== 'todos'): ?>
                    no filtro <strong><?= e((string) $selectedType); ?></strong>
                <?php endif; ?>
            <?php endif; ?>
        </p>
    </article>

    <article class="panel-card municipalities-overview-card warm">
        <p class="eyebrow">Escopo</p>
        <h2>Uma unica busca, quatro frentes do produto.</h2>
        <p>Use os filtros por tipo para alternar entre panorama completo e leitura focada em uma frente do acervo.</p>
    </article>
</section>

<?php if (($search ?? '') !== ''): ?>
    <?php
    $baseQuery = ['q' => $search];
    $filterChips = [
        ['key' => 'todos', 'label' => 'Tudo', 'count' => (int) ($totalResults ?? 0)],
        ['key' => 'municipios', 'label' => 'Municipios', 'count' => (int) (($counts['municipios'] ?? 0))],
        ['key' => 'turismo', 'label' => 'Turismo', 'count' => (int) (($counts['turismo'] ?? 0))],
        ['key' => 'educacao', 'label' => 'MAPI Educacao', 'count' => (int) (($counts['educacao'] ?? 0))],
        ['key' => 'midias', 'label' => 'Midias', 'count' => (int) (($counts['midias'] ?? 0))],
    ];
    ?>
    <section class="container filter-chip-row" aria-label="Filtros de busca">
        <?php foreach ($filterChips as $chip): ?>
            <?php $chipQuery = http_build_query($baseQuery + ['tipo' => $chip['key']]); ?>
            <a class="filter-chip <?= (($selectedType ?? 'todos') === $chip['key']) ? 'active' : ''; ?>" href="<?= e(url('busca?' . $chipQuery)); ?>">
                <?= e($chip['label']); ?>
                <span><?= (int) $chip['count']; ?></span>
            </a>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<?php if (($search ?? '') === ''): ?>
    <section class="container cards-grid">
        <article class="panel-card municipalities-empty-card">
            <p class="eyebrow">Comece por um termo</p>
            <h2>A busca ainda nao foi iniciada.</h2>
            <p>Tente o nome de um municipio, um tema cultural, um atrativo turistico ou uma palavra pedagogica.</p>
        </article>
    </section>
<?php elseif (($visibleResultCount ?? 0) === 0): ?>
    <section class="container cards-grid">
        <article class="panel-card municipalities-empty-card">
            <p class="eyebrow">Nenhum resultado</p>
            <h2>Nada publicado apareceu para essa palavra-chave neste recorte.</h2>
            <p>Vale tentar outro tipo de conteudo, um termo mais curto ou uma variacao do assunto.</p>
        </article>
    </section>
<?php endif; ?>

<?php if (($visibleSections['municipalities'] ?? false) && ($results['municipalities'] ?? []) !== []): ?>
    <section class="container section-heading">
        <p class="eyebrow">Municipios</p>
        <h2>Paginas territoriais encontradas para sua busca.</h2>
    </section>
    <section class="container cards-grid">
        <?php foreach ($results['municipalities'] as $municipality): ?>
            <article class="panel-card municipality-card municipality-card-rich">
                <p class="eyebrow"><?= e($municipality['estado_sigla']); ?></p>
                <h2><?= e($municipality['nome']); ?></h2>
                <p><?= e($municipality['descricao_curta'] ?: 'Pagina municipal publicada no acervo.'); ?></p>
                <a class="button button-outline" href="<?= e(url('municipio/' . $municipality['slug'])); ?>">Abrir municipio</a>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<?php if (($visibleSections['points'] ?? false) && ($results['points'] ?? []) !== []): ?>
    <section class="container section-heading">
        <p class="eyebrow">Turismo</p>
        <h2>Atrativos e pontos de interesse relacionados ao termo buscado.</h2>
    </section>
    <section class="container cards-grid">
        <?php foreach ($results['points'] as $point): ?>
            <article class="panel-card municipality-card">
                <p class="eyebrow"><?= e($point['municipio_nome'] . ' | ' . $point['estado_sigla']); ?></p>
                <h2><?= e($point['nome']); ?></h2>
                <p><?= e($point['descricao'] ?: 'Ponto turistico publicado no acervo.'); ?></p>
                <div class="municipality-card-tags">
                    <span class="story-pill"><?= e($point['categoria'] ?: 'Turismo'); ?></span>
                </div>
                <a class="button button-outline" href="<?= e(url('turismo/' . $point['id_ponto_turistico'])); ?>">Abrir atrativo</a>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<?php if (($visibleSections['activities'] ?? false) && ($results['activities'] ?? []) !== []): ?>
    <section class="container section-heading">
        <p class="eyebrow">MAPI Educacao</p>
        <h2>Atividades pedagogicas que cruzam com o tema buscado.</h2>
    </section>
    <section class="container cards-grid">
        <?php foreach ($results['activities'] as $activity): ?>
            <article class="panel-card municipality-card">
                <p class="eyebrow"><?= e($activity['municipio_nome'] . ' | ' . $activity['disciplina']); ?></p>
                <h2><?= e($activity['titulo']); ?></h2>
                <p><?= e($activity['descricao'] ?: 'Atividade pedagogica publicada no acervo.'); ?></p>
                <div class="municipality-card-tags">
                    <?php if (!empty($activity['ano_escolar'])): ?>
                        <span class="story-pill"><?= e($activity['ano_escolar']); ?></span>
                    <?php endif; ?>
                </div>
                <a class="button button-outline" href="<?= e(url('mapi-educacao/atividade/' . $activity['id_atividade'])); ?>">Abrir atividade</a>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<?php if (($visibleSections['media'] ?? false) && ($results['media'] ?? []) !== []): ?>
    <section class="container section-heading">
        <p class="eyebrow">Galeria</p>
        <h2>Midias publicadas que ajudam a ampliar a leitura do territorio.</h2>
    </section>
    <section class="container cards-grid">
        <?php foreach ($results['media'] as $item): ?>
            <article class="panel-card municipality-card">
                <p class="eyebrow"><?= e($item['municipio_nome'] . ' | ' . $item['tipo']); ?></p>
                <h2><?= e($item['titulo']); ?></h2>
                <p><?= e($item['descricao'] ?: 'Midia publicada no acervo.'); ?></p>
                <a class="button button-outline" href="<?= e(url('midia/' . $item['id_midia'])); ?>">Abrir midia</a>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>
