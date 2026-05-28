<section class="container page-header">
    <p class="eyebrow">Instalacao inicial</p>
    <h1>O projeto ainda nao encontrou a base pronta.</h1>
    <p>O frontend ja existe, mas o banco precisa ser importado para liberar login, municipios, quizzes e painel administrativo.</p>
</section>

<section class="container setup-grid">
    <article class="panel-card setup-card-main">
        <p class="eyebrow">Status atual</p>
        <h2>Diagnostico do ambiente</h2>
        <ul class="feature-list">
            <li>Banco configurado: <strong><?= e((string) ($databaseStatus['database'] ?? '-')); ?></strong></li>
            <li>Conexao com MySQL: <strong><?= !empty($databaseStatus['can_connect']) ? 'ok' : 'pendente'; ?></strong></li>
            <li>Schema principal: <strong><?= !empty($databaseStatus['has_schema']) ? 'ok' : 'faltando'; ?></strong></li>
        </ul>

        <?php if (!empty($databaseStatus['error'])): ?>
            <div class="alert alert-warning">
                <?= e((string) $databaseStatus['error']); ?>
            </div>
        <?php endif; ?>
    </article>

    <article class="panel-card warm-panel">
        <p class="eyebrow">Seed de teste</p>
        <h2>Credenciais previstas depois da importacao</h2>
        <p><strong>Email:</strong> <code>admin@mapiconecta.local</code></p>
        <p><strong>Senha:</strong> <code>admin123</code></p>
    </article>
</section>

<section class="container section-heading">
    <p class="eyebrow">Como resolver</p>
    <h2>Importe o schema e volte a testar os menus.</h2>
</section>

<section class="container setup-steps">
    <article class="panel-card">
        <span class="journey-step">1</span>
        <h3>Abra o phpMyAdmin</h3>
        <p>No XAMPP, abra o MySQL e entre no phpMyAdmin local.</p>
    </article>

    <article class="panel-card">
        <span class="journey-step">2</span>
        <h3>Importe o schema</h3>
        <p>Use o arquivo <code>database/schema.sql</code> para criar o banco, as tabelas e os dados iniciais.</p>
    </article>

    <article class="panel-card">
        <span class="journey-step">3</span>
        <h3>Teste a home e o login</h3>
        <p>Depois da importacao, a home passa a exibir estatisticas reais e o painel admin fica acessivel.</p>
    </article>
</section>

<?php if (!empty($databaseStatus['missing_tables'])): ?>
    <section class="container section-heading">
        <p class="eyebrow">Tabelas ausentes</p>
        <h2>Estas tabelas ainda nao foram encontradas na base atual.</h2>
    </section>

    <section class="container cards-grid">
        <?php foreach ($databaseStatus['missing_tables'] as $table): ?>
            <article class="panel-card">
                <h3><code><?= e((string) $table); ?></code></h3>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<section class="container final-cta">
    <div>
        <p class="eyebrow">Arquivos uteis</p>
        <h2>O setup rapido ja esta documentado no projeto.</h2>
    </div>
    <div class="hero-actions">
        <a class="button" href="<?= e(url('')); ?>">Voltar ao inicio</a>
        <a class="button button-outline" href="<?= e(url('entrar')); ?>">Ir para login</a>
    </div>
</section>
