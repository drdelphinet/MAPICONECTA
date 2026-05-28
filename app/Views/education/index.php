<section class="container page-header">
    <p class="eyebrow">Area pedagogica</p>
    <h1>MAPI Educacao</h1>
    <p>Uma frente educacional para organizar roteiros, desafios e atividades por municipio, disciplina e ano escolar.</p>
</section>

<section class="container education-hero">
    <article class="panel-card education-hero-main">
        <p class="eyebrow">Proposta</p>
        <h2>Ensinar cultura piauiense com contexto local, exploracao ativa e linguagem acessivel.</h2>
        <p>O objetivo desta area e aproximar professores, estudantes e visitantes de conteudos organizados por municipio, disciplina e finalidade pedagogica.</p>
    </article>

    <article class="panel-card education-hero-side">
        <p class="eyebrow">Tipos de conteudo</p>
        <ul class="feature-list">
            <li>Roteiro de estudo</li>
            <li>Plano de aula</li>
            <li>Atividade de pesquisa</li>
            <li>Quiz por municipio</li>
            <li>Texto de apoio</li>
            <li>Desafio cultural</li>
        </ul>
    </article>
</section>

<section class="container toolbar-card">
    <form method="get" action="<?= e(url('mapi-educacao')); ?>" class="filter-grid">
        <label class="field">
            <span>Municipio</span>
            <select name="municipio">
                <option value="">Todos</option>
                <?php foreach ($municipalities as $municipality): ?>
                    <option value="<?= e($municipality['slug']); ?>" <?= ($selectedMunicipalitySlug === $municipality['slug']) ? 'selected' : ''; ?>>
                        <?= e($municipality['nome']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="field">
            <span>Disciplina</span>
            <select name="disciplina">
                <option value="">Todas</option>
                <?php foreach ($disciplineOptions as $option): ?>
                    <option value="<?= e($option); ?>" <?= ($selectedDiscipline === $option) ? 'selected' : ''; ?>>
                        <?= e($option); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="field">
            <span>Ano escolar</span>
            <select name="ano">
                <option value="">Todos</option>
                <?php foreach ($schoolYearOptions as $option): ?>
                    <option value="<?= e($option); ?>" <?= ($selectedSchoolYear === $option) ? 'selected' : ''; ?>>
                        <?= e($option); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <div class="toolbar-actions align-end">
            <button type="submit" class="button">Filtrar</button>
            <a class="button button-outline" href="<?= e(url('mapi-educacao')); ?>">Limpar</a>
        </div>
    </form>
</section>

<section class="container section-heading">
    <p class="eyebrow">Atividades publicadas</p>
    <h2>Materiais que ja podem ser usados para aula, pesquisa ou exploracao orientada.</h2>
</section>

<section class="container cards-grid">
    <?php if ($activities === []): ?>
        <article class="panel-card municipalities-empty-card">
            <p class="eyebrow">Sem resultados</p>
            <h2>Nenhuma atividade publicada apareceu com esse filtro.</h2>
            <p>Voce pode ajustar os filtros ou seguir para municipios e quizzes para continuar a exploracao.</p>
            <div class="hero-actions">
                <a class="button" href="<?= e(url('mapi-educacao')); ?>">Limpar filtros</a>
                <a class="button button-outline" href="<?= e(url('municipios')); ?>">Ver municipios</a>
            </div>
        </article>
    <?php endif; ?>

    <?php foreach ($activities as $activity): ?>
        <article class="panel-card education-activity-card">
            <p class="eyebrow"><?= e($activity['disciplina']); ?><?php if (!empty($activity['ano_escolar'])): ?> | <?= e($activity['ano_escolar']); ?><?php endif; ?></p>
            <h2><?= e($activity['titulo']); ?></h2>
            <p><?= e($activity['descricao'] ?: 'Atividade pedagogica publicada para uso orientado por municipio.'); ?></p>
            <div class="municipality-card-tags">
                <span class="story-pill"><?= e($activity['municipio_nome']); ?></span>
                <?php if (!empty($activity['objetivo'])): ?>
                    <span class="story-pill">Objetivo definido</span>
                <?php endif; ?>
                <?php if (!empty($activity['avaliacao'])): ?>
                    <span class="story-pill">Com avaliacao</span>
                <?php endif; ?>
            </div>
            <p><strong>Objetivo:</strong> <?= e($activity['objetivo'] ?: 'Objetivo em consolidacao editorial.'); ?></p>
            <p><strong>Metodologia:</strong> <?= e($activity['metodologia'] ?: 'Metodologia em consolidacao editorial.'); ?></p>
            <div class="hero-actions">
                <a class="button" href="<?= e(url('mapi-educacao/atividade/' . $activity['id_atividade'])); ?>">Ver atividade</a>
                <a class="button button-outline" href="<?= e(url('municipio/' . $activity['municipio_slug'])); ?>">Ver municipio</a>
                <a class="button button-soft" href="<?= e(url('mapa')); ?>">Continuar exploracao</a>
            </div>
        </article>
    <?php endforeach; ?>
</section>

<section class="container section-heading">
    <p class="eyebrow">Fluxo pedagogico</p>
    <h2>O uso pensado no documento combina exploracao, leitura orientada e verificacao.</h2>
</section>

<section class="container journey-grid">
    <article class="panel-card journey-card">
        <span class="journey-step">1</span>
        <h3>Escolha um municipio</h3>
        <p>O mapa e a lista publica funcionam como ponto de partida para a aula ou para estudo autonomo.</p>
    </article>

    <article class="panel-card journey-card">
        <span class="journey-step">2</span>
        <h3>Leia por secao</h3>
        <p>Historia, geografia, economia, cultura e educacao organizam a leitura de forma mais clara para diferentes disciplinas.</p>
    </article>

    <article class="panel-card journey-card">
        <span class="journey-step">3</span>
        <h3>Responda o quiz</h3>
        <p>O quiz fecha o ciclo de aprendizagem com participacao ativa, memoria e pontuacao.</p>
    </article>

    <article class="panel-card journey-card">
        <span class="journey-step">4</span>
        <h3>Expanda a trilha</h3>
        <p>Depois, o usuario pode seguir para outros municipios, comparar regioes e construir repertorio territorial.</p>
    </article>
</section>

<section class="container final-cta">
    <div>
        <p class="eyebrow">Proximo ciclo</p>
        <h2>Esta area ja pode evoluir para PDFs, download de materiais e mais atividades publicadas.</h2>
    </div>
    <div class="hero-actions">
        <a class="button" href="<?= e(url('mapa')); ?>">Usar o mapa</a>
        <a class="button button-outline" href="<?= e(url('municipios')); ?>">Explorar municipios</a>
    </div>
</section>
