<?php
$heroSlides = array_values(array_filter(
    $featuredMedia ?? [],
    static fn (array $item): bool => (($item['tipo'] ?? '') === 'imagem') && trim((string) ($item['url_arquivo'] ?? '')) !== ''
));
$heroSlides = array_slice($heroSlides, 0, 8);
$heroSlidesLoop = count($heroSlides) > 1 ? array_merge($heroSlides, $heroSlides) : $heroSlides;
?>

<section class="hero hero-home">
    <div class="container hero-grid">
        <div class="hero-copy-column">
            <p class="eyebrow">Piaui em mapa, memoria e descoberta</p>
            <h1>Explore municipios, cultura, turismo e aprendizagem em uma experiencia viva do territorio piauiense.</h1>
            <p class="hero-copy">
                Descubra cidades, percorra o mapa, abra galerias, visite atrativos e avance por quizzes
                e conteudos conectados ao que torna cada municipio unico.
            </p>

            <div class="hero-actions">
                <a class="button" href="<?= e(url('mapa')); ?>">Abrir mapa</a>
                <a class="button" href="<?= e(url('municipios')); ?>">Explorar municipios</a>
                <a class="button button-soft" href="<?= e(url('mapi-educacao')); ?>">MAPI Educacao</a>
            </div>

            <div class="hero-microstats">
                <article>
                    <strong><?= (int) ($homeStats['publishedMunicipalities'] ?? 0); ?> municipios publicados</strong>
                    <span>Conteudo publico pronto para explorar</span>
                </article>
                <article>
                    <strong><?= (int) ($homeStats['mappedMunicipalities'] ?? 0); ?> no mapa</strong>
                    <span>Marcadores ativos para navegacao direta</span>
                </article>
                <article>
                    <strong><?= (int) ($homeStats['quizzes'] ?? 0); ?> quizzes</strong>
                    <span>Camada de interacao e aprendizado</span>
                </article>
                <article>
                    <strong><?= count($heroSlides); ?> destaques visuais</strong>
                    <span>Imagens publicadas do acervo municipal</span>
                </article>
            </div>
        </div>

        <div class="hero-visual hero-slideshow-shell">
            <?php if ($heroSlidesLoop !== []): ?>
                <div class="hero-slideshow">
                    <div class="hero-slideshow-track">
                        <?php foreach ($heroSlidesLoop as $slide): ?>
                            <a class="hero-slide-card" href="<?= e(url('midia/' . $slide['id_midia'])); ?>">
                                <img
                                    src="<?= e((string) $slide['url_arquivo']); ?>"
                                    alt="<?= e((string) ($slide['texto_alternativo'] ?: $slide['titulo'])); ?>"
                                >
                                <div class="hero-slide-overlay">
                                    <span><?= e((string) $slide['municipio_nome']); ?></span>
                                    <strong><?= e((string) $slide['titulo']); ?></strong>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <article class="hero-feature-card hero-feature-main">
                    <p class="eyebrow">Acervo visual</p>
                    <h2>O mapa, os municipios e as galerias trabalham juntos para revelar o Piaui em camadas.</h2>
                    <p>Abra cidades, encontre simbolos, paisagens, atrativos e trilhas de aprendizagem em um mesmo fluxo.</p>
                </article>
            <?php endif; ?>

            <div class="hero-feature-stack">
                <article class="hero-feature-card">
                    <span class="feature-kicker">Cidades</span>
                    <h3>Paginas publicas com identidade, contexto e exploracao local</h3>
                </article>

                <article class="hero-feature-card warm">
                    <span class="feature-kicker">Roteiros</span>
                    <h3>Quizzes, turismo e midias para circular pelo estado com mais repertorio</h3>
                </article>
            </div>
        </div>
    </div>
</section>

<?php if (!$databaseReady): ?>
    <section class="container toolbar-card">
        <p><strong>Base indisponivel no momento.</strong> Verifique a configuracao do banco para carregar municipios, quizzes e galerias.</p>
    </section>
<?php endif; ?>

<?php if (($user ?? null) !== null): ?>
    <section class="container section-heading">
        <p class="eyebrow">Seu MAPI</p>
        <h2>Retome seus percursos e continue explorando.</h2>
    </section>

    <section class="container my-mapi-grid">
        <article class="panel-card">
            <p class="eyebrow">Favoritos</p>
            <h2>Municipios salvos</h2>
            <?php if (($favoriteMunicipalities ?? []) === []): ?>
                <p>Adicione municipios aos favoritos para montar sua propria rota de descoberta.</p>
            <?php else: ?>
                <div class="story-band-list">
                    <?php foreach (array_slice($favoriteMunicipalities, 0, 4) as $municipality): ?>
                        <a class="story-pill" href="<?= e(url('municipio/' . $municipality['slug'])); ?>"><?= e($municipality['nome']); ?></a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </article>

        <article class="panel-card warm-panel">
            <p class="eyebrow">Visitados</p>
            <h2>Ultimos acessos marcados</h2>
            <?php if (($visitedMunicipalities ?? []) === []): ?>
                <p>Marque municipios visitados para construir seu historico de exploracao.</p>
            <?php else: ?>
                <div class="story-band-list">
                    <?php foreach (array_slice($visitedMunicipalities, 0, 4) as $municipality): ?>
                        <a class="story-pill" href="<?= e(url('municipio/' . $municipality['slug'])); ?>"><?= e($municipality['nome']); ?></a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </article>
    </section>
<?php endif; ?>

<section class="container home-grid-3 home-highlights">
    <article class="panel-card audience-card">
        <h2>Mapa interativo</h2>
        <p>Navegue pelo Piaui a partir do territorio e entre rapidamente nas paginas publicas dos municipios.</p>
    </article>

    <article class="panel-card audience-card">
        <h2>Acervo municipal</h2>
        <p>Historia, cultura, educacao, turismo e galerias reunidos em uma leitura clara de cada cidade.</p>
    </article>

    <article class="panel-card audience-card">
        <h2>Aprendizagem ativa</h2>
        <p>Quizzes e trilhas ajudam a transformar exploracao em participacao, memoria e descoberta.</p>
    </article>
</section>

<?php if ($featuredMunicipalities !== []): ?>
    <section class="container section-heading">
        <p class="eyebrow">Municipios em destaque</p>
        <h2>Escolha um ponto de partida no acervo publico.</h2>
    </section>

    <section class="container cards-grid">
        <?php foreach ($featuredMunicipalities as $municipality): ?>
            <article class="panel-card municipality-card municipality-card-rich">
                <p class="eyebrow"><?= e($municipality['estado_sigla']); ?></p>
                <h2><?= e($municipality['nome']); ?></h2>
                <p><?= e($municipality['descricao_curta'] ?: 'Conteudo municipal publicado para exploracao cultural, educativa e turistica.'); ?></p>
                <div class="municipality-card-tags">
                    <span class="story-pill">Historia</span>
                    <span class="story-pill">Cultura</span>
                    <span class="story-pill">Turismo</span>
                </div>
                <a class="button button-outline" href="<?= e(url('municipio/' . $municipality['slug'])); ?>">Explorar municipio</a>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<section class="container split-showcase">
    <?php if ($featuredQuizzes !== []): ?>
        <div>
            <div class="section-heading section-heading-inline">
                <p class="eyebrow">Quizzes</p>
                <h2>Aprenda jogando.</h2>
            </div>

            <section class="cards-grid compact-grid">
                <?php foreach ($featuredQuizzes as $quiz): ?>
                    <article class="panel-card feature-card">
                        <p class="eyebrow"><?= e($quiz['municipio_nome']); ?></p>
                        <h2><?= e($quiz['titulo']); ?></h2>
                        <p><?= e($quiz['descricao'] ?: 'Quiz publico disponivel para explorar um municipio do Piaui.'); ?></p>
                        <a class="button button-outline" href="<?= e(url('quiz/' . $quiz['id_quiz'])); ?>">Responder quiz</a>
                    </article>
                <?php endforeach; ?>
            </section>
        </div>
    <?php endif; ?>

    <?php if (($featuredPoints ?? []) !== []): ?>
        <div>
            <div class="section-heading section-heading-inline">
                <p class="eyebrow">Atrativos</p>
                <h2>Roteiros que pedem visita.</h2>
            </div>

            <section class="cards-grid compact-grid">
                <?php foreach ($featuredPoints as $point): ?>
                    <article class="panel-card municipality-card">
                        <p class="eyebrow"><?= e($point['municipio_nome'] . ' | ' . $point['estado_sigla']); ?></p>
                        <h2><?= e($point['nome']); ?></h2>
                        <p><?= e($point['descricao'] ?: 'Atrativo publicado no acervo turistico do municipio.'); ?></p>
                        <a class="button button-outline" href="<?= e(url('turismo/' . $point['id_ponto_turistico'])); ?>">Abrir atrativo</a>
                    </article>
                <?php endforeach; ?>
            </section>
        </div>
    <?php endif; ?>
</section>

<?php if (($featuredMedia ?? []) !== []): ?>
    <section class="container section-heading">
        <p class="eyebrow">Galeria</p>
        <h2>Imagens e registros para ampliar a experiencia do territorio.</h2>
    </section>

    <section class="container media-carousel-home" aria-label="Galeria em destaque">
        <div class="media-carousel-track">
            <?php foreach (array_slice($featuredMedia, 0, 8) as $item): ?>
                <article class="panel-card municipality-card media-carousel-card">
                    <p class="eyebrow"><?= e($item['municipio_nome'] . ' | ' . $item['tipo']); ?></p>
                    <h2><?= e($item['titulo']); ?></h2>
                    <?php if ($item['tipo'] === 'imagem'): ?>
                        <img src="<?= e($item['url_arquivo']); ?>" alt="<?= e($item['texto_alternativo'] ?: $item['titulo']); ?>" class="home-media-preview">
                    <?php endif; ?>
                    <p><?= e($item['descricao'] ?: 'Midia publicada no acervo municipal.'); ?></p>
                    <a class="button button-outline" href="<?= e(url('midia/' . $item['id_midia'])); ?>">Abrir midia</a>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<section class="container final-cta">
    <div>
        <p class="eyebrow">Comece agora</p>
        <h2>Abra o mapa, escolha um municipio e percorra o Piaui por conteudo, imagem e descoberta.</h2>
    </div>
    <div class="hero-actions">
        <a class="button" href="<?= e(url('mapa')); ?>">Explorar mapa</a>
        <a class="button button-outline" href="<?= e(url('municipios')); ?>">Ver municipios</a>
    </div>
</section>
