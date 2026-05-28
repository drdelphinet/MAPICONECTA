<section class="container page-header">
    <p class="eyebrow">Exploracao publica</p>
    <h1>Municipios</h1>
    <p>Encontre cidades publicadas e siga da descoberta inicial para historia, cultura, turismo, educacao e curiosidades locais.</p>
</section>

<section class="container toolbar-card">
    <form method="get" action="<?= e(url('municipios')); ?>" class="filter-grid">
        <label class="field">
            <span>Busca</span>
            <input type="text" name="busca" value="<?= e($search); ?>" placeholder="Ex.: Teresina, Oeiras, litoral">
        </label>

        <label class="field">
            <span>Estado</span>
            <select name="estado">
                <option value="">Todos</option>
                <?php foreach ($states as $state): ?>
                    <option value="<?= e($state['slug']); ?>" <?= ($selectedStateSlug === $state['slug']) ? 'selected' : ''; ?>>
                        <?= e($state['nome']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <div class="toolbar-actions align-end">
            <button type="submit" class="button">Buscar</button>
            <a class="button button-outline" href="<?= e(url('municipios')); ?>">Limpar</a>
        </div>
    </form>
</section>

<section class="container municipalities-overview">
    <article class="panel-card municipalities-overview-card">
        <p class="eyebrow">Resultado atual</p>
        <h2><?= (int) ($officialMunicipalities ?? $totalMunicipalities ?? count($municipalities)); ?> municipios no estado</h2>
        <p>
            <?= (int) ($totalMunicipalities ?? count($municipalities)); ?> no acervo publicado.
            Pagina <?= (int) ($currentPage ?? 1); ?> de <?= (int) ($totalPages ?? 1); ?>.
        </p>
    </article>

    <article class="panel-card municipalities-overview-card warm">
        <p class="eyebrow">Jornada sugerida</p>
        <h2>Abra um municipio e continue para conteudo e quiz.</h2>
        <p>A lista funciona como atalho para quem ja sabe o que procura, enquanto o mapa continua sendo a entrada mais exploratoria.</p>
    </article>
</section>

<section class="container cards-grid">
    <?php if ($municipalities === []): ?>
        <article class="panel-card municipalities-empty-card">
            <p class="eyebrow">Nenhum resultado</p>
            <h2>Nenhum municipio publicado apareceu para este filtro.</h2>
            <p>Voce pode ajustar a busca, limpar os filtros ou publicar novos municipios no painel administrativo.</p>
            <div class="hero-actions">
                <a class="button" href="<?= e(url('municipios')); ?>">Limpar filtros</a>
                <a class="button button-outline" href="<?= e(url('admin/municipios')); ?>">Ir para municipios no admin</a>
            </div>
        </article>
    <?php endif; ?>

    <?php foreach ($municipalities as $municipality): ?>
        <?php
            $isFavorited = in_array((int) $municipality['id_municipio'], $favoriteMunicipalityIds ?? [], true);
            $isVisited = in_array((int) $municipality['id_municipio'], $visitedMunicipalityIds ?? [], true);
        ?>
        <article class="panel-card municipality-card municipality-card-rich">
            <p class="eyebrow"><?= e($municipality['estado_sigla']); ?></p>
            <h2><?= e($municipality['nome']); ?></h2>
            <p><?= e($municipality['descricao_curta'] ?: 'Conteudo introdutorio em construcao para este municipio.'); ?></p>

            <div class="municipality-card-tags">
                <span class="story-pill">Historia</span>
                <span class="story-pill">Cultura</span>
                <span class="story-pill">Turismo</span>
            </div>

            <div class="municipality-card-actions">
                <a class="button button-outline" href="<?= e(url('municipio/' . $municipality['slug'])); ?>">Explorar municipio</a>

                <?php if (($user ?? null) !== null): ?>
                    <form method="post" action="<?= e(url('municipio/' . $municipality['slug'] . '/favorito')); ?>" class="inline-form">
                        <input type="hidden" name="_token" value="<?= e(\App\Core\Csrf::token()); ?>">
                        <button type="submit" class="button button-soft"><?= $isFavorited ? 'Remover favorito' : 'Favoritar'; ?></button>
                    </form>

                    <form method="post" action="<?= e(url('municipio/' . $municipality['slug'] . '/visitado')); ?>" class="inline-form">
                        <input type="hidden" name="_token" value="<?= e(\App\Core\Csrf::token()); ?>">
                        <button type="submit" class="button button-soft"><?= $isVisited ? 'Desmarcar visita' : 'Marcar visitado'; ?></button>
                    </form>
                <?php else: ?>
                    <a class="link-button municipality-inline-link" href="<?= e(url('entrar')); ?>">Entrar para salvar progresso</a>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
</section>

<?php if (($totalPages ?? 1) > 1): ?>
    <?php
        $queryBase = [];
        if ($search !== '') {
            $queryBase['busca'] = $search;
        }
        if ($selectedStateSlug !== '') {
            $queryBase['estado'] = $selectedStateSlug;
        }
    ?>
    <section class="container pagination-row">
        <?php if (($currentPage ?? 1) > 1): ?>
            <?php $prevQuery = http_build_query($queryBase + ['pagina' => ((int) $currentPage - 1)]); ?>
            <a class="button button-outline" href="<?= e(url('municipios' . ($prevQuery !== '' ? '?' . $prevQuery : ''))); ?>">Pagina anterior</a>
        <?php endif; ?>

        <div class="pagination-summary">
            <strong><?= (int) $currentPage; ?></strong>
            <span>de <?= (int) $totalPages; ?></span>
        </div>

        <?php if (($currentPage ?? 1) < ($totalPages ?? 1)): ?>
            <?php $nextQuery = http_build_query($queryBase + ['pagina' => ((int) $currentPage + 1)]); ?>
            <a class="button button-outline" href="<?= e(url('municipios' . ($nextQuery !== '' ? '?' . $nextQuery : ''))); ?>">Proxima pagina</a>
        <?php endif; ?>
    </section>
<?php endif; ?>
