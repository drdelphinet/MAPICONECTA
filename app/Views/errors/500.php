<section class="container error-page">
    <p class="eyebrow">Erro interno</p>
    <h1>Encontramos um problema ao processar sua solicitacao.</h1>
    <p>Verifique a configuracao inicial do projeto e a conexao com o banco de dados.</p>

    <?php if (!empty($debug) && isset($exception)): ?>
        <pre class="debug-box"><?= e($exception->getMessage()); ?></pre>
    <?php endif; ?>

    <a class="button" href="<?= e(url('')); ?>">Voltar ao inicio</a>
</section>
