<section class="container page-header">
    <p class="eyebrow">Quiz por municipio</p>
    <h1><?= e($quiz['titulo']); ?></h1>
    <p><?= e($quiz['descricao'] ?: 'Responda as perguntas e descubra quanto voce conhece este municipio.'); ?></p>
</section>

<section class="container quiz-hero">
    <article class="panel-card quiz-hero-main">
        <p class="eyebrow">Municipio relacionado</p>
        <h2><?= e($quiz['municipio_nome']); ?></h2>
        <p>Use este quiz como extensao da visita ao municipio: leia o conteudo, responda com calma e depois volte para aprofundar o que faltar.</p>
    </article>

    <article class="panel-card quiz-hero-side">
        <p class="eyebrow">Antes de começar</p>
        <ul class="feature-list">
            <li>As perguntas sao de multipla escolha.</li>
            <li>Visitantes podem responder normalmente.</li>
            <li>Usuarios logados salvam pontuacao e historico.</li>
        </ul>
    </article>
</section>

<section class="container toolbar-card">
    <p><strong>Municipio:</strong> <a href="<?= e(url('municipio/' . $quiz['municipio_slug'])); ?>"><?= e($quiz['municipio_nome']); ?></a></p>
    <?php if (!$user): ?>
        <p class="muted-line">Visitantes podem responder normalmente. Para salvar pontuacao, historico e medalhas, entre na sua conta.</p>
    <?php endif; ?>
</section>

<section class="container form-card">
    <form method="post" action="<?= e(url('quiz/' . $quiz['id_quiz'] . '/responder')); ?>" class="stack-form">
        <input type="hidden" name="_token" value="<?= e(\App\Core\Csrf::token()); ?>">

        <?php foreach ($questions as $index => $question): ?>
            <article class="quiz-question-card quiz-question-card-rich">
                <div class="quiz-question-topline">
                    <p class="eyebrow">Pergunta <?= $index + 1; ?></p>
                    <span class="badge"><?= (int) $question['pontos']; ?> pts</span>
                </div>
                <h2><?= e($question['pergunta']); ?></h2>

                <label class="quiz-option">
                    <input type="radio" name="pergunta_<?= (int) $question['id_pergunta']; ?>" value="a" required>
                    <span>A. <?= e($question['alternativa_a']); ?></span>
                </label>
                <label class="quiz-option">
                    <input type="radio" name="pergunta_<?= (int) $question['id_pergunta']; ?>" value="b">
                    <span>B. <?= e($question['alternativa_b']); ?></span>
                </label>
                <label class="quiz-option">
                    <input type="radio" name="pergunta_<?= (int) $question['id_pergunta']; ?>" value="c">
                    <span>C. <?= e($question['alternativa_c']); ?></span>
                </label>
                <label class="quiz-option">
                    <input type="radio" name="pergunta_<?= (int) $question['id_pergunta']; ?>" value="d">
                    <span>D. <?= e($question['alternativa_d']); ?></span>
                </label>
            </article>
        <?php endforeach; ?>

        <div class="toolbar-actions">
            <button type="submit" class="button">Finalizar quiz</button>
            <a class="button button-outline" href="<?= e(url('municipio/' . $quiz['municipio_slug'])); ?>">Voltar ao municipio</a>
        </div>
    </form>
</section>
