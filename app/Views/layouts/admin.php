<?php

$currentUser = $user ?? \App\Core\Auth::user();
$flashMessage = \App\Core\Session::getFlash('message');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Painel Administrativo'); ?> | MAPI CONECTA</title>
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')); ?>">
</head>
<body class="theme-admin">
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <a class="brand" href="<?= e(url('admin')); ?>">MAPI CONECTA</a>
            <p class="sidebar-user"><?= e($currentUser['nome'] ?? 'Administrador'); ?></p>
            <p class="sidebar-role"><?= e($currentUser['role_name'] ?? 'Perfil'); ?></p>

            <nav class="admin-nav" aria-label="Administracao">
                <a href="<?= e(url('admin')); ?>">Dashboard</a>
                <a href="<?= e(url('admin/estados')); ?>">Estados</a>
                <a href="<?= e(url('admin/municipios')); ?>">Municipios</a>
                <a href="<?= e(url('admin/turismo')); ?>">Pontos turisticos</a>
                <a href="<?= e(url('admin/midias')); ?>">Midias</a>
                <a href="<?= e(url('admin/educacao')); ?>">MAPI Educacao</a>
                <a href="<?= e(url('admin/quizzes')); ?>">Quizzes</a>
                <a href="<?= e(url('admin/curadoria')); ?>">Curadoria</a>
                <a href="<?= e(url('meu-mapi')); ?>">Meu MAPI</a>
                <a href="<?= e(url('')); ?>">Area publica</a>
            </nav>

            <form method="post" action="<?= e(url('sair')); ?>">
                <input type="hidden" name="_token" value="<?= e(\App\Core\Csrf::token()); ?>">
                <button type="submit" class="button button-outline full-width">Sair</button>
            </form>
        </aside>

        <main class="admin-content">
            <?php if ($flashMessage): ?>
                <div class="alert alert-<?= e($flashMessage['type'] ?? 'info'); ?>">
                    <?= e($flashMessage['text'] ?? ''); ?>
                </div>
            <?php endif; ?>

            <?= $content; ?>
        </main>
    </div>
</body>
</html>
