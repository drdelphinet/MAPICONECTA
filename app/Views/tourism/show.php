<?php
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => url('')],
    ['label' => 'Busca', 'url' => url('busca')],
    ['label' => $point['municipio_nome'], 'url' => url('municipio/' . $point['municipio_slug'])],
    ['label' => $point['nome']],
];
require app_path('app/Views/partials/breadcrumbs.php');
?>

<section class="container page-header">
    <p class="eyebrow">Turismo local</p>
    <h1><?= e($point['nome']); ?></h1>
    <p><?= e($point['descricao'] ?: 'Ponto turistico publicado para complementar a exploracao do municipio.'); ?></p>
</section>

<?php
$shareEntityTitle = $point['nome'];
$shareTitle = 'Compartilhe este atrativo com um clique';
$shareDescription = 'Leve este ponto turistico para roteiros, grupos, redes sociais e materiais impressos sem perder o caminho de volta ao municipio.';
$sharePublicUrl = $publicUrl;
$shareQrImageUrl = $qrImageUrl;
$shareQrTrackingUrl = $qrTrackingUrl;
$shareQrTitle = 'QR principal do municipio';
$shareQrDescription = 'Este QR abre a pagina publica do municipio relacionado, servindo como porta de entrada rastreavel para o contexto completo.';
require app_path('app/Views/partials/share-showcase.php');
?>

<section class="container education-hero">
    <article class="panel-card education-hero-main">
        <p class="eyebrow">Municipio relacionado</p>
        <h2><?= e($point['municipio_nome']); ?></h2>
        <p>Este atrativo faz parte da leitura territorial e cultural do municipio publicado no acervo.</p>
    </article>

    <article class="panel-card education-hero-side">
        <p class="eyebrow">Ficha rapida</p>
        <ul class="feature-list">
            <li>Categoria: <?= e($point['categoria'] ?: 'Nao informada'); ?></li>
            <li>Entrada: <?= e($point['valor_entrada'] ?: 'Nao informado'); ?></li>
            <li>Estado: <?= e($point['estado_sigla']); ?></li>
        </ul>
    </article>
</section>

<section class="container toolbar-card">
    <div class="hero-actions">
        <a class="button" href="<?= e(url('municipio/' . $point['municipio_slug'])); ?>">Ver municipio</a>
        <a class="button button-soft" href="<?= e(url('municipios')); ?>">Explorar outros municipios</a>
        <a class="button button-outline" href="<?= e(url('mapa')); ?>">Abrir mapa</a>
    </div>
    <p class="muted-line">Link publico deste ponto: <code><?= e($publicUrl); ?></code></p>
</section>

<section class="container section-grid">
    <article class="panel-card">
        <h2>Descricao</h2>
        <p><?= nl2br(e($point['descricao'] ?: 'Descricao em consolidacao editorial.')); ?></p>
    </article>

    <article class="panel-card">
        <h2>Endereco</h2>
        <p><?= nl2br(e($point['endereco'] ?: 'Endereco nao informado.')); ?></p>
    </article>

    <article class="panel-card">
        <h2>Visita</h2>
        <p><strong>Horario:</strong> <?= e($point['horario_funcionamento'] ?: 'Nao informado'); ?></p>
        <p><strong>Entrada:</strong> <?= e($point['valor_entrada'] ?: 'Nao informado'); ?></p>
        <p><strong>Contato:</strong> <?= e($point['contato'] ?: 'Nao informado'); ?></p>
    </article>

    <article class="panel-card">
        <h2>Origem no acervo</h2>
        <p><strong>Fonte:</strong> <?= e($point['fonte'] ?: 'Cadastro MAPI'); ?></p>
        <p><strong>Municipio:</strong> <a href="<?= e(url('municipio/' . $point['municipio_slug'])); ?>"><?= e($point['municipio_nome']); ?></a></p>
    </article>

    <?php if (!empty($point['imagem'])): ?>
        <article class="panel-card">
            <h2>Imagem de apoio</h2>
            <img src="<?= e($point['imagem']); ?>" alt="<?= e($point['nome']); ?>" style="max-width: 100%; border-radius: 1rem;">
        </article>
    <?php endif; ?>

    <article class="panel-card">
        <h2>QR do municipio</h2>
        <p>Se este ponto estiver sendo usado em material impresso, o QR principal do municipio continua servindo como porta de entrada rastreavel.</p>
        <p><code><?= e($qrTrackingUrl); ?></code></p>
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

    <article class="panel-card">
        <h2>Midias relacionadas</h2>
        <?php if (($relatedMedia ?? []) === []): ?>
            <p>Ainda nao ha midias publicadas vinculadas a este municipio.</p>
        <?php else: ?>
            <?php foreach ($relatedMedia as $item): ?>
                <div class="quiz-inline-item">
                    <h3><?= e($item['titulo']); ?></h3>
                    <p><?= e($item['descricao'] ?: 'Midia de apoio para aprofundar a leitura do territorio.'); ?></p>
                    <?php if ($item['tipo'] === 'imagem'): ?>
                        <img src="<?= e($item['url_arquivo']); ?>" alt="<?= e($item['texto_alternativo'] ?: $item['titulo']); ?>" style="max-width: 100%; border-radius: 1rem; margin-top: 1rem;">
                    <?php endif; ?>
                    <div class="hero-actions">
                        <a class="button button-outline" href="<?= e(url('midia/' . $item['id_midia'])); ?>">Ver detalhes</a>
                        <a class="button button-soft" href="<?= e($item['url_arquivo']); ?>" target="_blank" rel="noopener noreferrer">Abrir arquivo</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </article>
</section>

<section class="container section-heading">
    <p class="eyebrow">Continue explorando</p>
    <h2>Outros caminhos publicados dentro do mesmo territorio.</h2>
</section>

<section class="container cards-grid">
    <article class="panel-card municipality-card">
        <p class="eyebrow">Municipio base</p>
        <h2><?= e($point['municipio_nome']); ?></h2>
        <p>Volte para a pagina principal do municipio e navegue por historia, cultura, educacao e outros blocos do acervo.</p>
        <a class="button button-outline" href="<?= e(url('municipio/' . $point['municipio_slug'])); ?>">Abrir municipio</a>
    </article>

    <?php if (($otherPoints ?? []) !== []): ?>
        <?php $nextPoint = $otherPoints[0]; ?>
        <article class="panel-card municipality-card">
            <p class="eyebrow">Outro atrativo</p>
            <h2><?= e($nextPoint['nome']); ?></h2>
            <p><?= e($nextPoint['descricao'] ?: 'Outro ponto turistico publicado no mesmo municipio.'); ?></p>
            <a class="button button-outline" href="<?= e(url('turismo/' . $nextPoint['id_ponto_turistico'])); ?>">Abrir atrativo</a>
        </article>
    <?php endif; ?>

    <?php if (($relatedActivities ?? []) !== []): ?>
        <?php $nextActivity = $relatedActivities[0]; ?>
        <article class="panel-card municipality-card">
            <p class="eyebrow">MAPI Educacao</p>
            <h2><?= e($nextActivity['titulo']); ?></h2>
            <p><?= e($nextActivity['descricao'] ?: 'Atividade pedagogica relacionada ao mesmo territorio.'); ?></p>
            <a class="button button-outline" href="<?= e(url('mapi-educacao/atividade/' . $nextActivity['id_atividade'])); ?>">Abrir atividade</a>
        </article>
    <?php endif; ?>

    <?php if (($relatedQuizzes ?? []) !== []): ?>
        <?php $nextQuiz = $relatedQuizzes[0]; ?>
        <article class="panel-card municipality-card">
            <p class="eyebrow">Quiz</p>
            <h2><?= e($nextQuiz['titulo']); ?></h2>
            <p><?= e($nextQuiz['descricao'] ?: 'Quiz publicado para reforcar a exploracao do mesmo municipio.'); ?></p>
            <a class="button button-outline" href="<?= e(url('quiz/' . $nextQuiz['id_quiz'])); ?>">Responder quiz</a>
        </article>
    <?php endif; ?>
</section>
