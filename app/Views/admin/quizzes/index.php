<section class="page-header">
    <p class="eyebrow">Quizzes e gamificacao</p>
    <h1>Quizzes</h1>
    <p>Gerencie quizzes por municipio e avance para o cadastro de perguntas e publicacao.</p>
</section>

<section class="toolbar-card">
    <div class="toolbar-actions">
        <a class="button" href="<?= e(url('admin/quizzes/novo')); ?>">Novo quiz</a>
    </div>
</section>

<section class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Titulo</th>
                <th>Municipio</th>
                <th>Status</th>
                <th>Acoes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($quizzes as $quiz): ?>
                <tr>
                    <td><?= e($quiz['titulo']); ?></td>
                    <td><?= e($quiz['municipio_nome']); ?></td>
                    <td><span class="badge"><?= e($quiz['status_curadoria']); ?></span></td>
                    <td>
                        <a href="<?= e(url('admin/quizzes/' . $quiz['id_quiz'] . '/editar')); ?>">Editar</a>
                        <span class="divider-dot">•</span>
                        <a href="<?= e(url('admin/quizzes/' . $quiz['id_quiz'] . '/perguntas')); ?>">Perguntas</a>
                        <?php if ($quiz['status_curadoria'] === 'publicado'): ?>
                            <span class="divider-dot">•</span>
                            <a href="<?= e(url('quiz/' . $quiz['id_quiz'])); ?>" target="_blank" rel="noopener noreferrer">Abrir publico</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
