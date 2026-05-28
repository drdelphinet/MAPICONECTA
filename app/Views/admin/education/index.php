<section class="page-header">
    <p class="eyebrow">Area pedagogica</p>
    <h1>Atividades pedagogicas</h1>
    <p>Gerencie roteiros, planos de aula, desafios culturais e materiais vinculados aos municipios.</p>
</section>

<section class="toolbar-card">
    <div class="toolbar-actions">
        <a class="button" href="<?= e(url('admin/educacao/nova')); ?>">Nova atividade</a>
    </div>
</section>

<section class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Titulo</th>
                <th>Municipio</th>
                <th>Disciplina</th>
                <th>Status</th>
                <th>Acoes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($activities as $activity): ?>
                <tr>
                    <td><?= e($activity['titulo']); ?></td>
                    <td><?= e($activity['municipio_nome'] . ' (' . $activity['estado_sigla'] . ')'); ?></td>
                    <td><?= e($activity['disciplina']); ?></td>
                    <td><span class="badge"><?= e($activity['status_curadoria']); ?></span></td>
                    <td>
                        <a href="<?= e(url('admin/educacao/' . $activity['id_atividade'] . '/editar')); ?>">Editar</a>
                        <?php if ($activity['status_curadoria'] === 'publicado'): ?>
                            <span class="divider-dot">|</span>
                            <a href="<?= e(url('mapi-educacao/atividade/' . $activity['id_atividade'])); ?>" target="_blank" rel="noopener noreferrer">Abrir publico</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
