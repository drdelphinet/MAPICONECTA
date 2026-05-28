<?php

$flashMessage = \App\Core\Session::getFlash('message');
$currentUser = \App\Core\Auth::user();
$headerLogoFile = 'logo_vertical.png';
$footerHeroLogoFile = 'logo_horizontal branca.png';
$footerLogos = [
    [
        'label' => 'Sistema',
        'title' => 'MAPI CONECTA',
        'file' => 'logo_vertical.png',
    ],
    [
        'label' => 'Governo do estado',
        'title' => 'Governo do Estado do Piaui',
        'file' => 'logo_piaui.png',
    ],
    [
        'label' => 'Escola realizadora',
        'title' => 'Instituicao do projeto',
        'file' => 'logo_escola.jpg',
    ],
];
$headerLogoExists = is_file(app_path('public/assets/img/' . $headerLogoFile));
$footerHeroLogoExists = is_file(app_path('public/assets/img/' . $footerHeroLogoFile));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2f5daa">
    <title><?= e($title ?? config('app.name', 'MAPI CONECTA')); ?></title>
    <link rel="icon" type="image/png" sizes="32x32" href="<?= e(asset('assets/img/favicon-32.png')); ?>">
    <link rel="icon" type="image/png" sizes="256x256" href="<?= e(asset('assets/img/favicon.png')); ?>">
    <link rel="shortcut icon" type="image/png" href="<?= e(asset('assets/img/favicon-32.png')); ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= e(asset('assets/img/apple-touch-icon.png')); ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')); ?>">
    <?php foreach (($leafletStyles ?? []) as $styleUrl): ?>
        <link rel="stylesheet" href="<?= e($styleUrl); ?>">
    <?php endforeach; ?>
</head>
<body class="theme-public">
    <a class="skip-link" href="#conteudo">Ir para conteudo</a>

    <div class="accessibility-bar">
        <div class="container accessibility-inner">
            <div class="accessibility-copy">
                <strong>Acessibilidade</strong>
                <span>Mais contraste, leitura mais confortavel e navegacao mais clara.</span>
            </div>

            <div class="accessibility-actions" aria-label="Controles de acessibilidade">
                <button type="button" class="accessibility-button" data-font-scale="-1">A-</button>
                <button type="button" class="accessibility-button" data-font-scale="1">A+</button>
                <button type="button" class="accessibility-button" data-toggle-contrast="true">Alto contraste</button>
                <button type="button" class="accessibility-button" data-toggle-reading="true">Modo leitura</button>
            </div>
        </div>
    </div>

    <header class="site-header">
        <div class="container header-inner">
            <div class="brand-block">
                <a class="brand" href="<?= e(url('')); ?>">
                    <?php if ($headerLogoExists): ?>
                        <img src="<?= e(asset('assets/img/' . $headerLogoFile)); ?>" alt="MAPI CONECTA" class="brand-logo">
                    <?php endif; ?>
                </a>
            </div>

            <div class="header-tools">
                <form method="get" action="<?= e(url('busca')); ?>" class="header-search-form" role="search" aria-label="Busca no acervo">
                    <input type="search" name="q" value="<?= e((string) ($_GET['q'] ?? '')); ?>" placeholder="Buscar no acervo">
                    <button type="submit">Buscar</button>
                </form>

                <nav class="site-nav" aria-label="Principal">
                    <a href="<?= e(url('')); ?>">Inicio</a>
                    <a href="<?= e(url('mapa')); ?>">Mapa</a>
                    <a href="<?= e(url('municipios')); ?>">Municipios</a>
                    <a href="<?= e(url('mapi-educacao')); ?>">MAPI Educacao</a>
                    <a href="<?= e(url('ranking')); ?>">Ranking</a>
                    <a href="<?= e(url('meu-mapi')); ?>">Meu MAPI</a>
                    <a href="<?= e(url('admin')); ?>">Admin</a>
                    <?php if ($currentUser): ?>
                        <form method="post" action="<?= e(url('sair')); ?>" class="inline-form">
                            <input type="hidden" name="_token" value="<?= e(\App\Core\Csrf::token()); ?>">
                            <button type="submit" class="link-button">Sair</button>
                        </form>
                    <?php else: ?>
                        <a class="button button-outline" href="<?= e(url('entrar')); ?>">Entrar</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <?php if ($flashMessage): ?>
        <div class="container">
            <div class="alert alert-<?= e($flashMessage['type'] ?? 'info'); ?>">
                <?= e($flashMessage['text'] ?? ''); ?>
            </div>
        </div>
    <?php endif; ?>

    <main id="conteudo">
        <?= $content; ?>
    </main>

    <footer class="site-footer">
        <div class="container site-footer-inner">
            <div class="site-footer-copy">
                <?php if ($footerHeroLogoExists): ?>
                    <img src="<?= e(asset('assets/img/' . $footerHeroLogoFile)); ?>" alt="MAPI CONECTA" class="site-footer-hero-logo">
                <?php else: ?>
                    <h2>MAPI CONECTA</h2>
                <?php endif; ?>
                <p>Plataforma publica para aproximar territorio, educacao, cultura e turismo do Piaui com linguagem acessivel e navegacao conectada.</p>
            </div>

            <div class="site-footer-brand-grid">
                <?php foreach ($footerLogos as $logo): ?>
                    <?php
                    $imagePath = app_path('public/assets/img/' . $logo['file']);
                    $imageUrl = asset('assets/img/' . $logo['file']);
                    $hasImage = is_file($imagePath);
                    ?>
                    <article class="footer-brand-card">
                        <span class="footer-brand-label"><?= e($logo['label']); ?></span>
                        <div class="footer-brand-visual<?= $hasImage ? '' : ' is-placeholder'; ?>">
                            <?php if ($hasImage): ?>
                                <img src="<?= e($imageUrl); ?>" alt="<?= e($logo['title']); ?>" class="footer-brand-image">
                            <?php else: ?>
                                <span><?= e($logo['title']); ?></span>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </footer>

    <script>
        window.MAPI_BASE_URL = <?= json_encode(rtrim((string) url(''), '/'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    </script>
    <script src="<?= e(asset('assets/js/public-ui.js')); ?>"></script>
    <?php foreach (($pageScripts ?? []) as $scriptUrl): ?>
        <script src="<?= e($scriptUrl); ?>"></script>
    <?php endforeach; ?>
</body>
</html>
