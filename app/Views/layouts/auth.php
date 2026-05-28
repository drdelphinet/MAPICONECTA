<?php

$flashMessage = $message ?? \App\Core\Session::getFlash('message');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Entrar'); ?> | MAPI CONECTA</title>
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')); ?>">
</head>
<body class="theme-auth">
    <main class="auth-shell">
        <section class="auth-panel auth-panel-brand">
            <p class="eyebrow">Projeto educativo e turistico</p>
            <h1>MAPI CONECTA</h1>
            <p>Base pronta para mapa interativo, curadoria editorial, quizzes por municipio e expansao para todo o Brasil.</p>
            <ul class="feature-list">
                <li>Area publica responsiva</li>
                <li>Meu MAPI para usuarios logados</li>
                <li>Painel administrativo com curadoria</li>
            </ul>
        </section>

        <section class="auth-panel auth-panel-form">
            <?php if ($flashMessage): ?>
                <div class="alert alert-<?= e($flashMessage['type'] ?? 'info'); ?>">
                    <?= e($flashMessage['text'] ?? ''); ?>
                </div>
            <?php endif; ?>

            <?= $content; ?>
        </section>
    </main>
</body>
</html>
