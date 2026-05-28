<?php
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => url('')],
    ['label' => 'Municipios', 'url' => url('municipios')],
    ['label' => $municipality['nome']],
];
require app_path('app/Views/partials/breadcrumbs.php');
?>

<section class="container page-header">
    <p class="eyebrow"><?= e($municipality['estado_sigla']); ?> | Municipio publicado</p>
    <h1><?= e($municipality['nome']); ?></h1>
    <p><?= e($municipality['descricao_curta'] ?: 'Conteudo editorial em expansao para este municipio.'); ?></p>
</section>

<?php
$heroSourceGallery = ($scenicGallery ?? []) !== [] ? ($scenicGallery ?? []) : ($visualGallery ?? []);
$heroGallery = array_slice($heroSourceGallery, 0, 3);
$heroImage = $heroGallery[0] ?? null;
$heroMapPoints = $mapPoints ?? [];
?>

<section class="container municipality-immersive-hero">
    <article class="panel-card municipality-visual-card municipality-visual-card-primary">
        <div class="municipality-visual-copy">
            <p class="eyebrow">Paisagem e identidade</p>
            <h2><?= e($heroImage['title'] ?? ('Visao visual de ' . $municipality['nome'])); ?></h2>
            <p><?= e($heroImage['description'] ?? 'Assim que o acervo visual cresce, esta area passa a mostrar paisagens, simbolos, patrimonio e cenas do cotidiano do municipio.'); ?></p>
            <div class="municipality-card-tags">
                <span class="story-pill"><?= e((string) (($heroImage['label'] ?? 'Acervo visual'))); ?></span>
                <?php if (!empty($heroImage['credit'])): ?>
                    <span class="story-pill">Credito: <?= e((string) $heroImage['credit']); ?></span>
                <?php endif; ?>
            </div>
            <?php if (!empty($heroImage['link_url'])): ?>
                <a class="button button-outline" href="<?= e((string) $heroImage['link_url']); ?>"><?= e((string) ($heroImage['link_label'] ?? 'Abrir')); ?></a>
            <?php endif; ?>
        </div>

        <?php if ($heroImage !== null): ?>
            <img
                class="municipality-hero-image"
                src="<?= e((string) $heroImage['image_url']); ?>"
                alt="<?= e((string) ($heroImage['alt'] ?? $heroImage['title'])); ?>"
            >
        <?php else: ?>
            <div class="municipality-hero-placeholder">
                <strong>Galeria em expansao</strong>
                <p>Cadastre imagem principal do municipio, fotos de atrativos e midias publicadas para transformar esta abertura em uma entrada visual forte.</p>
            </div>
        <?php endif; ?>
    </article>

    <article class="panel-card municipality-map-card">
        <div class="municipality-visual-copy">
            <p class="eyebrow">Mapa local</p>
            <h2>Territorio e pontos de visita</h2>
            <p>Use este mapa como porta de entrada para localizar a sede do municipio e os atrativos turisticos que ja tiverem coordenadas publicadas.</p>
        </div>

        <?php if (!empty($hasMapData)): ?>
            <div
                id="municipality-local-map"
                class="municipality-local-map"
                data-map-points="<?= e(json_encode($heroMapPoints, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?>"
                data-map-title="<?= e($municipality['nome']); ?>"
            ></div>
            <div class="municipality-map-legend">
                <span class="story-pill">Municipio: <?= e($municipality['nome']); ?></span>
                <span class="story-pill">Atrativos no mapa: <?= (int) max(0, count($heroMapPoints) - 1); ?></span>
            </div>
        <?php else: ?>
            <div class="municipality-hero-placeholder municipality-map-empty">
                <strong>Mapa local aguardando coordenadas</strong>
                <p>Quando o municipio ou os pontos turisticos tiverem latitude e longitude, esta area passa a mostrar a localizacao deles automaticamente.</p>
            </div>
        <?php endif; ?>
    </article>
</section>

<?php if (($heroGallery ?? []) !== []): ?>
    <section class="container municipality-photo-strip">
        <?php foreach (array_slice($heroGallery, 1) as $visual): ?>
            <article class="panel-card municipality-photo-strip-card">
                <img src="<?= e((string) $visual['image_url']); ?>" alt="<?= e((string) ($visual['alt'] ?? $visual['title'])); ?>">
                <div>
                    <p class="eyebrow"><?= e((string) ($visual['label'] ?? 'Acervo visual')); ?></p>
                    <h3><?= e((string) $visual['title']); ?></h3>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<section class="container toolbar-card">
    <div class="editorial-meter">
        <div>
            <strong>Prontidao editorial: <?= (int) ($municipality['editorial_authored_score'] ?? 0); ?>%</strong>
            <p>
                <?php if (($municipality['editorial_status'] ?? '') === 'autoral'): ?>
                    Este municipio ja tem um conjunto mais consistente de secoes com conteudo autoral.
                <?php elseif (($municipality['editorial_status'] ?? '') === 'em_curadoria'): ?>
                    Este municipio ja mistura base estruturada com conteudo em curadoria.
                <?php else: ?>
                    Este municipio ja pode ser explorado, mas boa parte do conteudo ainda esta na base editorial inicial.
                <?php endif; ?>
            </p>
        </div>
        <span class="badge editorial-badge editorial-badge-<?= e((string) ($municipality['editorial_status'] ?? 'essencial')); ?>">
            <?= e(str_replace('_', ' ', (string) ($municipality['editorial_status'] ?? 'essencial'))); ?>
        </span>
    </div>
</section>

<section class="container municipality-hero-meta">
    <div class="stat-card">
        <span>Populacao</span>
        <strong class="stat-card-value"><?= e($municipality['populacao'] ? number_format((int) $municipality['populacao'], 0, ',', '.') : '-'); ?></strong>
        <small class="stat-card-meta">
            Fonte: <?= e((string) (($municipality['facts_evidence']['fonte'] ?? 'Cadastro MAPI'))); ?><br>
            Referencia: <?= e((string) (($municipality['facts_evidence']['populacao'] ?? 'n/d'))); ?>
        </small>
    </div>
    <div class="stat-card">
        <span>Area</span>
        <strong class="stat-card-value"><?= e($municipality['area'] ? (string) $municipality['area'] . ' km2' : '-'); ?></strong>
        <small class="stat-card-meta">
            Fonte: <?= e((string) (($municipality['facts_evidence']['fonte'] ?? 'Cadastro MAPI'))); ?><br>
            Referencia: <?= e((string) (($municipality['facts_evidence']['area'] ?? 'n/d'))); ?>
        </small>
    </div>
    <div class="stat-card">
        <span>Densidade</span>
        <strong class="stat-card-value"><?= e($municipality['densidade_demografica'] ? (string) $municipality['densidade_demografica'] . ' hab/km2' : '-'); ?></strong>
        <small class="stat-card-meta">
            Fonte: <?= e((string) (($municipality['facts_evidence']['fonte'] ?? 'Cadastro MAPI'))); ?><br>
            Referencia: <?= e((string) (($municipality['facts_evidence']['densidade_demografica'] ?? 'n/d'))); ?>
        </small>
    </div>
    <div class="stat-card">
        <span>Gentilico</span>
        <strong class="stat-card-value stat-card-value-text"><?= e($municipality['gentilico'] ?: '-'); ?></strong>
        <small class="stat-card-meta">
            Fonte: <?= e((string) (($municipality['facts_evidence']['fonte'] ?? 'Cadastro MAPI'))); ?><br>
            Referencia: <?= e((string) (($municipality['facts_evidence']['gentilico'] ?? 'n/d'))); ?>
        </small>
    </div>
    <div class="stat-card">
        <span>Codigo IBGE</span>
        <strong class="stat-card-value"><?= e((string) $municipality['codigo_ibge']); ?></strong>
        <small class="stat-card-meta">
            Fonte: IBGE Localidades<br>
            Identificacao oficial
        </small>
    </div>
</section>

<?php if (($municipality['facts_status'] ?? 'vazio') !== 'vazio'): ?>
    <section class="container toolbar-card">
        <p class="muted-line">
            Dados factuais disponiveis: <?= (int) ($municipality['facts_ready_fields'] ?? 0); ?>/<?= (int) ($municipality['facts_total_fields'] ?? 0); ?>
            campos preenchidos.
            <?php if (!empty($municipality['fonte_dados'])): ?>
                Fonte principal: <?= e((string) (($municipality['facts_evidence']['fonte'] ?? $municipality['fonte_dados']))); ?>.
            <?php endif; ?>
            <?php if (!empty($municipality['facts_access_date'])): ?>
                Coleta no portal oficial: <?= e(date('d/m/Y', strtotime((string) $municipality['facts_access_date']))); ?>.
            <?php endif; ?>
            <?php if (!empty($municipality['facts_source_url'])): ?>
                <a href="<?= e((string) $municipality['facts_source_url']); ?>" target="_blank" rel="noopener noreferrer">Abrir fonte oficial</a>.
            <?php endif; ?>
        </p>
    </section>
<?php endif; ?>

<section class="container municipality-intro-grid">
    <article class="panel-card municipality-intro-card">
        <p class="eyebrow">Leitura guiada</p>
        <h2>Um hub local para aprender com contexto.</h2>
        <p>Use as secoes abaixo como trilha de exploracao: historia para origem, geografia para territorio, economia para dinamica local e cultura para identidade.</p>
    </article>

    <article class="panel-card municipality-intro-card warm">
        <p class="eyebrow">Proximo passo</p>
        <h2>Feche a visita com um quiz.</h2>
        <p>Quando houver quiz publicado, ele ajuda a transformar a leitura em participacao ativa e memoria de longo prazo.</p>
    </article>
</section>

<?php if (($visualGallery ?? []) !== []): ?>
    <section class="container section-heading">
        <p class="eyebrow">Galeria visual</p>
        <h2>Imagens para sentir o territorio antes mesmo de terminar a leitura.</h2>
    </section>

    <section class="container municipality-gallery-grid">
        <?php foreach ($visualGallery as $visual): ?>
            <article class="panel-card municipality-gallery-card">
                <div class="municipality-gallery-media">
                    <img src="<?= e((string) $visual['image_url']); ?>" alt="<?= e((string) ($visual['alt'] ?? $visual['title'])); ?>">
                    <span class="municipality-gallery-badge"><?= e((string) ($visual['label'] ?? 'Acervo visual')); ?></span>
                </div>
                <div class="municipality-gallery-copy">
                    <h3><?= e((string) $visual['title']); ?></h3>
                    <p><?= e((string) ($visual['description'] ?? 'Registro visual publicado para apoiar a exploracao do municipio.')); ?></p>
                    <?php if (!empty($visual['credit'])): ?>
                        <p class="municipality-gallery-meta">Credito: <?= e((string) $visual['credit']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($visual['link_url'])): ?>
                        <a class="button button-outline" href="<?= e((string) $visual['link_url']); ?>"><?= e((string) ($visual['link_label'] ?? 'Abrir')); ?></a>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<?php if (($symbolGallery ?? []) !== []): ?>
    <section class="container section-heading">
        <p class="eyebrow">Simbolos do municipio</p>
        <h2>Bandeira, brasao e outros marcos civicos do territorio.</h2>
    </section>

    <section class="container municipality-symbol-grid">
        <?php foreach ($symbolGallery as $symbol): ?>
            <article class="panel-card municipality-symbol-card">
                <div class="municipality-symbol-media">
                    <img src="<?= e((string) $symbol['image_url']); ?>" alt="<?= e((string) ($symbol['alt'] ?? $symbol['title'])); ?>">
                </div>
                <div class="municipality-gallery-copy">
                    <p class="eyebrow"><?= e((string) ($symbol['label'] ?? 'Simbolo')); ?></p>
                    <h3><?= e((string) $symbol['title']); ?></h3>
                    <p><?= e((string) ($symbol['description'] ?? 'Simbolo visual publicado para reforcar a identidade do municipio.')); ?></p>
                    <?php if (!empty($symbol['credit'])): ?>
                        <p class="municipality-gallery-meta">Credito: <?= e((string) $symbol['credit']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($symbol['link_url'])): ?>
                        <a class="button button-outline" href="<?= e((string) $symbol['link_url']); ?>"><?= e((string) ($symbol['link_label'] ?? 'Abrir')); ?></a>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<?php if (!empty($user)): ?>
    <section class="container toolbar-card">
        <div class="hero-actions">
            <form method="post" action="<?= e(url('municipio/' . $municipality['slug'] . '/favorito')); ?>" class="inline-form">
                <input type="hidden" name="_token" value="<?= e(\App\Core\Csrf::token()); ?>">
                <button type="submit" class="button <?= !empty($isFavorited) ? 'button-soft' : ''; ?>">
                    <?= !empty($isFavorited) ? 'Remover dos favoritos' : 'Salvar nos favoritos'; ?>
                </button>
            </form>

            <form method="post" action="<?= e(url('municipio/' . $municipality['slug'] . '/visitado')); ?>" class="inline-form">
                <input type="hidden" name="_token" value="<?= e(\App\Core\Csrf::token()); ?>">
                <button type="submit" class="button button-outline">
                    <?= !empty($isVisited) ? 'Desmarcar visitado' : 'Marcar como visitado'; ?>
                </button>
            </form>
        </div>
    </section>
<?php endif; ?>

<section class="container section-grid">
    <article class="panel-card">
        <h2>Historia</h2>
        <p><?= nl2br(e($municipality['historia'] ?: 'Conteudo ainda nao publicado nesta secao.')); ?></p>
    </article>

    <article class="panel-card">
        <h2>Geografia</h2>
        <p><?= nl2br(e($municipality['geografia'] ?: 'Conteudo ainda nao publicado nesta secao.')); ?></p>
    </article>

    <article class="panel-card">
        <h2>Economia</h2>
        <p><?= nl2br(e($municipality['economia'] ?: 'Conteudo ainda nao publicado nesta secao.')); ?></p>
    </article>

    <article class="panel-card">
        <h2>Cultura</h2>
        <p><?= nl2br(e($municipality['cultura'] ?: 'Conteudo ainda nao publicado nesta secao.')); ?></p>
    </article>

    <article class="panel-card">
        <h2>Turismo</h2>
        <p><?= nl2br(e($municipality['turismo'] ?: 'Conteudo ainda nao publicado nesta secao.')); ?></p>
    </article>

    <article class="panel-card">
        <h2>Pontos turisticos</h2>
        <?php if (($points ?? []) === []): ?>
            <p>Ainda nao ha pontos turisticos publicados para este municipio.</p>
        <?php else: ?>
            <?php foreach ($points as $point): ?>
                <div class="quiz-inline-item municipality-point-item">
                    <?php if (!empty($point['imagem'])): ?>
                        <img
                            class="municipality-point-image"
                            src="<?= e((string) $point['imagem']); ?>"
                            alt="<?= e((string) $point['nome']); ?>"
                        >
                    <?php endif; ?>
                    <h3><?= e($point['nome']); ?></h3>
                    <p><?= e($point['descricao'] ?: 'Ponto turistico publicado para complementar a exploracao local.'); ?></p>
                    <p><strong>Categoria:</strong> <?= e($point['categoria'] ?: 'Nao informada'); ?></p>
                    <?php if (!empty($point['endereco'])): ?><p><strong>Endereco:</strong> <?= e($point['endereco']); ?></p><?php endif; ?>
                    <?php if (!empty($point['horario_funcionamento'])): ?><p><strong>Horario:</strong> <?= e($point['horario_funcionamento']); ?></p><?php endif; ?>
                    <?php if (!empty($point['valor_entrada'])): ?><p><strong>Entrada:</strong> <?= e($point['valor_entrada']); ?></p><?php endif; ?>
                    <?php if (!empty($point['contato'])): ?><p><strong>Contato:</strong> <?= e($point['contato']); ?></p><?php endif; ?>
                    <a class="button button-outline" href="<?= e(url('turismo/' . $point['id_ponto_turistico'])); ?>">Abrir ponto turistico</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </article>

    <article class="panel-card">
        <h2>Educacao</h2>
        <p><?= nl2br(e($municipality['educacao'] ?: 'Conteudo ainda nao publicado nesta secao.')); ?></p>
    </article>

    <article class="panel-card">
        <h2>Curiosidades</h2>
        <p><?= nl2br(e($municipality['curiosidades'] ?: 'Conteudo ainda nao publicado nesta secao.')); ?></p>
    </article>

    <article class="panel-card">
        <h2>Quizzes do municipio</h2>
        <?php if (($quizzes ?? []) === []): ?>
            <p>Ainda nao ha quiz publicado para este municipio.</p>
        <?php else: ?>
            <?php foreach ($quizzes as $quiz): ?>
                <div class="quiz-inline-item">
                    <h3><?= e($quiz['titulo']); ?></h3>
                    <p><?= e($quiz['descricao'] ?: 'Quiz publico disponivel para explorar este municipio.'); ?></p>
                    <a class="button button-outline" href="<?= e(url('quiz/' . $quiz['id_quiz'])); ?>">Responder quiz</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </article>

    <article class="panel-card">
        <h2>MAPI Educacao</h2>
        <?php if (($activities ?? []) === []): ?>
            <p>Ainda nao ha atividade pedagogica publicada para este municipio.</p>
            <a class="button button-outline" href="<?= e(url('mapi-educacao')); ?>">Abrir area pedagogica</a>
        <?php else: ?>
            <?php foreach ($activities as $activity): ?>
                <div class="quiz-inline-item">
                    <h3><?= e($activity['titulo']); ?></h3>
                    <p><?= e($activity['descricao'] ?: 'Atividade pedagogica vinculada a este municipio.'); ?></p>
                    <div class="hero-actions">
                        <a class="button button-outline" href="<?= e(url('mapi-educacao/atividade/' . $activity['id_atividade'])); ?>">Abrir atividade</a>
                        <a class="button button-soft" href="<?= e(url('mapi-educacao?municipio=' . $municipality['slug'])); ?>">Ver lista pedagogica</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </article>

    <article class="panel-card">
        <h2>Galeria de midias</h2>
        <?php if (($mediaItems ?? []) === []): ?>
            <p>Ainda nao ha midias publicadas para este municipio.</p>
        <?php else: ?>
            <?php foreach ($mediaItems as $item): ?>
                <div class="quiz-inline-item municipality-media-item">
                    <h3><?= e($item['titulo']); ?></h3>
                    <p><?= e($item['descricao'] ?: 'Midia publicada para complementar a leitura do municipio.'); ?></p>
                    <p><strong>Tipo:</strong> <?= e($item['tipo']); ?></p>
                    <?php if ($item['tipo'] === 'imagem'): ?>
                        <img class="municipality-media-image" src="<?= e($item['url_arquivo']); ?>" alt="<?= e($item['texto_alternativo'] ?: $item['titulo']); ?>">
                    <?php endif; ?>
                    <div class="hero-actions">
                        <a class="button button-outline" href="<?= e(url('midia/' . $item['id_midia'])); ?>">Ver detalhes</a>
                        <a class="button button-soft" href="<?= e($item['url_arquivo']); ?>" target="_blank" rel="noopener noreferrer">Abrir arquivo</a>
                    </div>
                    <?php if (!empty($item['credito'])): ?>
                        <p><strong>Credito:</strong> <?= e($item['credito']); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </article>

</section>

<?php
$hasExploreRoutes = ($points ?? []) !== [] || ($activities ?? []) !== [] || ($mediaItems ?? []) !== [] || ($quizzes ?? []) !== [];
?>
<?php if ($hasExploreRoutes): ?>
    <section class="container section-heading">
        <p class="eyebrow">Continue explorando</p>
        <h2>Escolha uma proxima trilha dentro de <?= e($municipality['nome']); ?>.</h2>
    </section>

    <section class="container cards-grid">
        <?php if (($points ?? []) !== []): ?>
            <?php $featuredPoint = $points[0]; ?>
            <article class="panel-card municipality-card">
                <p class="eyebrow">Turismo</p>
                <h2><?= e($featuredPoint['nome']); ?></h2>
                <p><?= e($featuredPoint['descricao'] ?: 'Ponto turistico publicado para complementar a exploracao local.'); ?></p>
                <a class="button button-outline" href="<?= e(url('turismo/' . $featuredPoint['id_ponto_turistico'])); ?>">Abrir atrativo</a>
            </article>
        <?php endif; ?>

        <?php if (($activities ?? []) !== []): ?>
            <?php $featuredActivity = $activities[0]; ?>
            <article class="panel-card municipality-card">
                <p class="eyebrow">MAPI Educacao</p>
                <h2><?= e($featuredActivity['titulo']); ?></h2>
                <p><?= e($featuredActivity['descricao'] ?: 'Atividade pedagogica vinculada a este municipio.'); ?></p>
                <a class="button button-outline" href="<?= e(url('mapi-educacao/atividade/' . $featuredActivity['id_atividade'])); ?>">Abrir atividade</a>
            </article>
        <?php endif; ?>

        <?php if (($mediaItems ?? []) !== []): ?>
            <?php $featuredMedia = $mediaItems[0]; ?>
            <article class="panel-card municipality-card">
                <p class="eyebrow">Galeria</p>
                <h2><?= e($featuredMedia['titulo']); ?></h2>
                <p><?= e($featuredMedia['descricao'] ?: 'Midia publicada para ampliar a leitura do municipio.'); ?></p>
                <a class="button button-outline" href="<?= e(url('midia/' . $featuredMedia['id_midia'])); ?>">Abrir midia</a>
            </article>
        <?php endif; ?>

        <?php if (($quizzes ?? []) !== []): ?>
            <?php $featuredQuiz = $quizzes[0]; ?>
            <article class="panel-card municipality-card">
                <p class="eyebrow">Quiz</p>
                <h2><?= e($featuredQuiz['titulo']); ?></h2>
                <p><?= e($featuredQuiz['descricao'] ?: 'Quiz publico disponivel para explorar este municipio.'); ?></p>
                <a class="button button-outline" href="<?= e(url('quiz/' . $featuredQuiz['id_quiz'])); ?>">Responder quiz</a>
            </article>
        <?php endif; ?>
    </section>
<?php endif; ?>

<?php
$shareEntityTitle = $municipality['nome'];
$shareTitle = 'Leve ' . $municipality['nome'] . ' para outras telas';
$shareDescription = 'Compartilhe esta pagina em redes sociais, copie o link direto ou use o QR Code para materiais impressos, aula e roteiro.';
$sharePublicUrl = $publicUrl;
$shareQrImageUrl = $qrImageUrl;
$shareQrTrackingUrl = $qrTrackingUrl;
$shareQrTitle = 'Entrada rapida por leitura';
$shareQrDescription = 'Use este QR em impresso, slide, banner ou roteiro para abrir o municipio direto pelo link rastreavel do projeto.';
require app_path('app/Views/partials/share-showcase.php');
?>
