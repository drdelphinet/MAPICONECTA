<section class="container page-header">
    <p class="eyebrow">Resultado</p>
    <h1><?= e($quiz['titulo']); ?></h1>
    <p>Voce acertou <?= (int) $correct; ?> de <?= (int) $total; ?> perguntas e fez <?= (int) $score; ?> pontos.</p>
</section>

<section class="container municipality-hero-meta">
    <div class="stat-card"><span>Acertos</span><strong><?= (int) $correct; ?>/<?= (int) $total; ?></strong></div>
    <div class="stat-card"><span>Pontos</span><strong><?= (int) $score; ?></strong></div>
    <div class="stat-card"><span>Progresso salvo</span><strong><?= $saved ? 'Sim' : 'Nao'; ?></strong></div>
</section>

<section class="container result-summary-grid">
    <article class="panel-card">
        <p class="eyebrow">Leitura do resultado</p>
        <h2>Use este retorno para continuar aprendendo.</h2>
        <p>O objetivo do quiz nao e apenas pontuar, mas reforcar o entendimento sobre o municipio e indicar o que vale revisar na pagina principal.</p>
    </article>

    <article class="panel-card warm-panel">
        <p class="eyebrow">Proximo passo</p>
        <h2>Volte ao municipio e aprofunde os pontos em que voce errou.</h2>
        <p>Historia, geografia, cultura e curiosidades continuam acessiveis para complementar a experiencia.</p>
    </article>
</section>

<?php if ($saved && $awardedMedals !== []): ?>
    <section class="container toolbar-card">
        <h2>Medalhas atuais</h2>
        <div class="medal-grid">
            <?php foreach ($awardedMedals as $medal): ?>
                <article class="panel-card medal-card">
                    <h3><?= e($medal['nome']); ?></h3>
                    <p><?= e($medal['descricao'] ?? ''); ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<section class="container cards-grid">
    <?php foreach ($results as $index => $result): ?>
        <article class="panel-card result-card <?= $result['acertou'] ? 'result-success' : 'result-warning'; ?>">
            <p class="eyebrow">Pergunta <?= $index + 1; ?></p>
            <h2><?= e($result['pergunta']); ?></h2>
            <p><strong>Sua resposta:</strong> <?= e($result['resposta_marcada'] !== '' ? strtoupper($result['resposta_marcada']) . '. ' . $result['alternativas'][$result['resposta_marcada']] : 'Nao respondida'); ?></p>
            <p><strong>Correta:</strong> <?= e(strtoupper($result['resposta_correta']) . '. ' . $result['alternativas'][$result['resposta_correta']]); ?></p>
            <p><?= e($result['explicacao'] ?: 'Sem explicacao adicional cadastrada.'); ?></p>
        </article>
    <?php endforeach; ?>
</section>

<section class="container final-cta">
    <div>
        <p class="eyebrow">Continuar explorando</p>
        <h2>Use o resultado como ponto de partida para conhecer mais municipios.</h2>
    </div>
    <div class="hero-actions">
        <a class="button" href="<?= e(url('municipio/' . $quiz['municipio_slug'])); ?>">Voltar ao municipio</a>
        <a class="button button-outline" href="<?= e(url('ranking')); ?>">Ver ranking</a>
    </div>
</section>
