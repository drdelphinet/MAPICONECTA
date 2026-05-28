<section class="page-header">
    <p class="eyebrow">Quizzes e gamificacao</p>
    <h1>Perguntas do quiz</h1>
    <p><strong><?= e($quiz['titulo']); ?></strong> • <?= e($quiz['municipio_nome']); ?></p>
</section>

<section class="toolbar-card">
    <div class="toolbar-actions">
        <a class="button" href="<?= e(url('admin/quizzes/' . $quiz['id_quiz'] . '/perguntas/nova')); ?>">Nova pergunta</a>
        <a class="button button-outline" href="<?= e(url('admin/quizzes/' . $quiz['id_quiz'] . '/editar')); ?>">Editar quiz</a>
    </div>
</section>

<section class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Ordem</th>
                <th>Pergunta</th>
                <th>Correta</th>
                <th>Pontos</th>
                <th>Status</th>
                <th>Acoes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($questions as $question): ?>
                <tr>
                    <td><?= (int) $question['ordem']; ?></td>
                    <td><?= e($question['pergunta']); ?></td>
                    <td><?= e(strtoupper($question['resposta_correta'])); ?></td>
                    <td><?= (int) $question['pontos']; ?></td>
                    <td><span class="badge"><?= e($question['status_curadoria']); ?></span></td>
                    <td><a href="<?= e(url('admin/quizzes/' . $quiz['id_quiz'] . '/perguntas/' . $question['id_pergunta'] . '/editar')); ?>">Editar</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
