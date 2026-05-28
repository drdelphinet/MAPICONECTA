<?php
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => url('')],
    ['label' => 'MAPI Educacao', 'url' => url('mapi-educacao')],
    ['label' => $activity['municipio_nome'], 'url' => url('municipio/' . $activity['municipio_slug'])],
    ['label' => $activity['titulo']],
];
require app_path('app/Views/partials/breadcrumbs.php');
?>

<section class="container page-header">
    <p class="eyebrow">MAPI Educacao</p>
    <h1><?= e($activity['titulo']); ?></h1>
    <p><?= e($activity['descricao'] ?: 'Atividade pedagogica publicada para apoiar leitura orientada do municipio.'); ?></p>
</section>

<?php
$shareEntityTitle = $activity['titulo'];
$shareTitle = 'Compartilhe esta atividade pedagogica';
$shareDescription = 'Use este link para circular a atividade em sala, grupos de professores, redes sociais ou materiais de apoio do territorio.';
$sharePublicUrl = $publicUrl;
$shareQrImageUrl = $qrImageUrl;
$shareQrTrackingUrl = $qrTrackingUrl;
$shareQrTitle = 'QR do municipio relacionado';
$shareQrDescription = 'Esse QR continua sendo a porta de entrada rastreavel para o municipio que sustenta a atividade e seu contexto territorial.';
require app_path('app/Views/partials/share-showcase.php');
?>

<section class="container education-hero">
    <article class="panel-card education-hero-main">
        <p class="eyebrow">Municipio relacionado</p>
        <h2><?= e($activity['municipio_nome']); ?></h2>
        <p>Esta atividade conecta leitura territorial, repertorio local e aplicacao pedagogica a partir do municipio publicado.</p>
    </article>

    <article class="panel-card education-hero-side">
        <p class="eyebrow">Ficha rapida</p>
        <ul class="feature-list">
            <li>Disciplina: <?= e($activity['disciplina']); ?></li>
            <li>Ano escolar: <?= e($activity['ano_escolar'] ?: 'Nao informado'); ?></li>
            <li>Status: publicado</li>
        </ul>
    </article>
</section>

<section class="container toolbar-card">
    <div class="hero-actions">
        <a class="button" href="<?= e(url('municipio/' . $activity['municipio_slug'])); ?>">Ver municipio</a>
        <a class="button button-outline" href="<?= e(url('mapi-educacao')); ?>">Voltar para atividades</a>
        <a class="button button-soft" href="<?= e(url('busca?q=' . urlencode($activity['municipio_nome']))); ?>">Buscar conteudos relacionados</a>
        <?php if (!empty($activity['arquivo_pdf'])): ?>
            <a class="button button-soft" href="<?= e((string) $activity['arquivo_pdf']); ?>" target="_blank" rel="noopener noreferrer">Baixar material</a>
        <?php endif; ?>
    </div>
    <p class="muted-line">Link publico desta atividade: <code><?= e($publicUrl); ?></code></p>
</section>

<section class="container section-grid">
    <article class="panel-card">
        <h2>Objetivo</h2>
        <p><?= nl2br(e($activity['objetivo'] ?: 'Objetivo em consolidacao editorial.')); ?></p>
    </article>

    <article class="panel-card">
        <h2>Metodologia</h2>
        <p><?= nl2br(e($activity['metodologia'] ?: 'Metodologia em consolidacao editorial.')); ?></p>
    </article>

    <article class="panel-card">
        <h2>Recursos necessarios</h2>
        <p><?= nl2br(e($activity['recursos_necessarios'] ?: 'Recursos em consolidacao editorial.')); ?></p>
    </article>

    <article class="panel-card">
        <h2>Desenvolvimento</h2>
        <p><?= nl2br(e($activity['desenvolvimento'] ?: 'Desenvolvimento em consolidacao editorial.')); ?></p>
    </article>

    <article class="panel-card">
        <h2>Avaliacao</h2>
        <p><?= nl2br(e($activity['avaliacao'] ?: 'Avaliacao em consolidacao editorial.')); ?></p>
    </article>

    <article class="panel-card">
        <h2>Exploracao complementar</h2>
        <p>Depois da leitura, avance para o municipio relacionado e use os quizzes publicados como fechamento da trilha pedagogica.</p>
        <div class="hero-actions">
            <a class="button button-outline" href="<?= e(url('municipio/' . $activity['municipio_slug'])); ?>">Abrir municipio</a>
        </div>
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
    <h2>Mais entradas publicadas para continuar a trilha territorial.</h2>
</section>

<section class="container cards-grid">
    <article class="panel-card municipality-card">
        <p class="eyebrow">Municipio base</p>
        <h2><?= e($activity['municipio_nome']); ?></h2>
        <p>Abra a pagina municipal para cruzar esta atividade com historia, geografia, turismo e outras camadas do acervo.</p>
        <a class="button button-outline" href="<?= e(url('municipio/' . $activity['municipio_slug'])); ?>">Abrir municipio</a>
    </article>

    <?php if (($otherActivities ?? []) !== []): ?>
        <?php $nextActivity = $otherActivities[0]; ?>
        <article class="panel-card municipality-card">
            <p class="eyebrow">Outra atividade</p>
            <h2><?= e($nextActivity['titulo']); ?></h2>
            <p><?= e($nextActivity['descricao'] ?: 'Outra atividade pedagogica publicada no mesmo municipio.'); ?></p>
            <a class="button button-outline" href="<?= e(url('mapi-educacao/atividade/' . $nextActivity['id_atividade'])); ?>">Abrir atividade</a>
        </article>
    <?php endif; ?>

    <?php if (($relatedPoints ?? []) !== []): ?>
        <?php $nextPoint = $relatedPoints[0]; ?>
        <article class="panel-card municipality-card">
            <p class="eyebrow">Turismo</p>
            <h2><?= e($nextPoint['nome']); ?></h2>
            <p><?= e($nextPoint['descricao'] ?: 'Atrativo publicado no mesmo territorio.'); ?></p>
            <a class="button button-outline" href="<?= e(url('turismo/' . $nextPoint['id_ponto_turistico'])); ?>">Abrir atrativo</a>
        </article>
    <?php endif; ?>

    <?php if (($relatedMedia ?? []) !== []): ?>
        <?php $nextMedia = $relatedMedia[0]; ?>
        <article class="panel-card municipality-card">
            <p class="eyebrow">Galeria</p>
            <h2><?= e($nextMedia['titulo']); ?></h2>
            <p><?= e($nextMedia['descricao'] ?: 'Midia publicada para ampliar a leitura do mesmo municipio.'); ?></p>
            <a class="button button-outline" href="<?= e(url('midia/' . $nextMedia['id_midia'])); ?>">Abrir midia</a>
        </article>
    <?php endif; ?>
</section>
