<?php
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => url('')],
    ['label' => 'Busca', 'url' => url('busca')],
    ['label' => $mediaItem['municipio_nome'], 'url' => url('municipio/' . $mediaItem['municipio_slug'])],
    ['label' => $mediaItem['titulo']],
];
require app_path('app/Views/partials/breadcrumbs.php');
?>

<section class="container page-header">
    <p class="eyebrow">Galeria publica</p>
    <h1><?= e($mediaItem['titulo']); ?></h1>
    <p><?= e($mediaItem['descricao'] ?: 'Midia publicada para ampliar a leitura do municipio e do territorio.'); ?></p>
</section>

<?php
$shareEntityTitle = $mediaItem['titulo'];
$shareTitle = 'Compartilhe esta peca do acervo';
$shareDescription = 'Espalhe esta midia em redes, grupos, apresentacoes e roteiros, mantendo o acesso rapido ao conteudo publicado.';
$sharePublicUrl = $publicUrl;
$shareQrImageUrl = $qrImageUrl;
$shareQrTrackingUrl = $qrTrackingUrl;
$shareQrTitle = 'QR do municipio relacionado';
$shareQrDescription = 'Esse QR leva ao municipio do acervo e ajuda a manter a experiencia conectada ao territorio de origem da midia.';
require app_path('app/Views/partials/share-showcase.php');
?>

<section class="container education-hero">
    <article class="panel-card education-hero-main">
        <p class="eyebrow">Municipio relacionado</p>
        <h2><?= e($mediaItem['municipio_nome']); ?></h2>
        <p>Esta peca faz parte do acervo publico do municipio e pode apoiar exploracao cultural, pedagogica e turistica.</p>
    </article>

    <article class="panel-card education-hero-side">
        <p class="eyebrow">Ficha rapida</p>
        <ul class="feature-list">
            <li>Tipo: <?= e($mediaItem['tipo']); ?></li>
            <li>Estado: <?= e($mediaItem['estado_sigla']); ?></li>
            <li>Destaque: <?= (int) ($mediaItem['destaque'] ?? 0) === 1 ? 'sim' : 'nao'; ?></li>
        </ul>
    </article>
</section>

<section class="container toolbar-card">
    <div class="hero-actions">
        <a class="button" href="<?= e(url('municipio/' . $mediaItem['municipio_slug'])); ?>">Ver municipio</a>
        <a class="button button-soft" href="<?= e(url('busca?q=' . urlencode($mediaItem['municipio_nome']))); ?>">Buscar no mesmo territorio</a>
        <a class="button button-outline" href="<?= e((string) $mediaItem['url_arquivo']); ?>" target="_blank" rel="noopener noreferrer">Abrir arquivo original</a>
    </div>
    <p class="muted-line">Link publico desta midia: <code><?= e($publicUrl); ?></code></p>
</section>

<section class="container section-grid">
    <article class="panel-card">
        <h2>Descricao</h2>
        <p><?= nl2br(e($mediaItem['descricao'] ?: 'Descricao em consolidacao editorial.')); ?></p>
    </article>

    <article class="panel-card">
        <h2>Creditos e acessibilidade</h2>
        <p><strong>Credito:</strong> <?= e($mediaItem['credito'] ?: 'Nao informado'); ?></p>
        <p><strong>Texto alternativo:</strong> <?= e($mediaItem['texto_alternativo'] ?: 'Nao informado'); ?></p>
        <p><strong>Licenca:</strong> <?= e($mediaItem['licenca'] ?: 'Nao informada'); ?></p>
        <?php if (!empty($mediaItem['url_origem'])): ?>
            <p><strong>Origem:</strong> <a href="<?= e((string) $mediaItem['url_origem']); ?>" target="_blank" rel="noopener noreferrer">Abrir pagina de origem</a></p>
        <?php endif; ?>
    </article>

    <article class="panel-card">
        <h2>Arquivo</h2>
        <p>Tipo publicado: <?= e($mediaItem['tipo']); ?></p>
        <p>Use o botao abaixo para abrir o arquivo na fonte original.</p>
        <a class="button button-outline" href="<?= e((string) $mediaItem['url_arquivo']); ?>" target="_blank" rel="noopener noreferrer">Abrir arquivo</a>
    </article>

    <article class="panel-card">
        <h2>Origem no acervo</h2>
        <p><strong>Municipio:</strong> <a href="<?= e(url('municipio/' . $mediaItem['municipio_slug'])); ?>"><?= e($mediaItem['municipio_nome']); ?></a></p>
        <p><strong>Status:</strong> publicado</p>
        <?php if (!empty($mediaItem['url_origem'])): ?>
            <p><strong>Fonte externa:</strong> <a href="<?= e((string) $mediaItem['url_origem']); ?>" target="_blank" rel="noopener noreferrer"><?= e((string) $mediaItem['url_origem']); ?></a></p>
        <?php endif; ?>
    </article>

    <?php if ($mediaItem['tipo'] === 'imagem'): ?>
        <article class="panel-card">
            <h2>Visualizacao</h2>
            <img src="<?= e((string) $mediaItem['url_arquivo']); ?>" alt="<?= e($mediaItem['texto_alternativo'] ?: $mediaItem['titulo']); ?>" style="max-width: 100%; border-radius: 1rem;">
        </article>
    <?php endif; ?>

    <article class="panel-card">
        <h2>Pontos turisticos relacionados</h2>
        <?php if (($relatedPoints ?? []) === []): ?>
            <p>Ainda nao ha pontos turisticos publicados para este municipio.</p>
        <?php else: ?>
            <?php foreach ($relatedPoints as $point): ?>
                <div class="quiz-inline-item">
                    <h3><?= e($point['nome']); ?></h3>
                    <p><?= e($point['descricao'] ?: 'Ponto turistico publicado no mesmo municipio.'); ?></p>
                    <a class="button button-outline" href="<?= e(url('turismo/' . $point['id_ponto_turistico'])); ?>">Abrir ponto turistico</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </article>

    <article class="panel-card">
        <h2>Quizzes relacionados</h2>
        <?php if (($relatedQuizzes ?? []) === []): ?>
            <p>Ainda nao ha quiz publicado para este municipio.</p>
        <?php else: ?>
            <?php foreach ($relatedQuizzes as $quiz): ?>
                <div class="quiz-inline-item">
                    <h3><?= e($quiz['titulo']); ?></h3>
                    <p><?= e($quiz['descricao'] ?: 'Quiz publico vinculado ao mesmo municipio.'); ?></p>
                    <a class="button button-outline" href="<?= e(url('quiz/' . $quiz['id_quiz'])); ?>">Responder quiz</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </article>
</section>

<section class="container section-heading">
    <p class="eyebrow">Continue explorando</p>
    <h2>Mais camadas publicadas para seguir dentro do mesmo municipio.</h2>
</section>

<section class="container cards-grid">
    <article class="panel-card municipality-card">
        <p class="eyebrow">Municipio base</p>
        <h2><?= e($mediaItem['municipio_nome']); ?></h2>
        <p>Volte para a pagina do municipio e continue a exploracao territorial pelas demais frentes do produto.</p>
        <a class="button button-outline" href="<?= e(url('municipio/' . $mediaItem['municipio_slug'])); ?>">Abrir municipio</a>
    </article>

    <?php if (($otherMedia ?? []) !== []): ?>
        <?php $nextMedia = $otherMedia[0]; ?>
        <article class="panel-card municipality-card">
            <p class="eyebrow">Outra midia</p>
            <h2><?= e($nextMedia['titulo']); ?></h2>
            <p><?= e($nextMedia['descricao'] ?: 'Outra peca publicada no mesmo municipio.'); ?></p>
            <a class="button button-outline" href="<?= e(url('midia/' . $nextMedia['id_midia'])); ?>">Abrir midia</a>
        </article>
    <?php endif; ?>

    <?php if (($relatedActivities ?? []) !== []): ?>
        <?php $nextActivity = $relatedActivities[0]; ?>
        <article class="panel-card municipality-card">
            <p class="eyebrow">MAPI Educacao</p>
            <h2><?= e($nextActivity['titulo']); ?></h2>
            <p><?= e($nextActivity['descricao'] ?: 'Atividade pedagogica publicada no mesmo territorio.'); ?></p>
            <a class="button button-outline" href="<?= e(url('mapi-educacao/atividade/' . $nextActivity['id_atividade'])); ?>">Abrir atividade</a>
        </article>
    <?php endif; ?>

    <?php if (($relatedPoints ?? []) !== []): ?>
        <?php $nextPoint = $relatedPoints[0]; ?>
        <article class="panel-card municipality-card">
            <p class="eyebrow">Turismo</p>
            <h2><?= e($nextPoint['nome']); ?></h2>
            <p><?= e($nextPoint['descricao'] ?: 'Ponto turistico publicado no mesmo territorio.'); ?></p>
            <a class="button button-outline" href="<?= e(url('turismo/' . $nextPoint['id_ponto_turistico'])); ?>">Abrir atrativo</a>
        </article>
    <?php endif; ?>
</section>
