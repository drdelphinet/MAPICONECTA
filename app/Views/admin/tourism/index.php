<section class="page-header">
    <p class="eyebrow">Turismo</p>
    <h1>Pontos turisticos</h1>
    <p>Cadastre atrativos, lugares de visita, patrimonio e experiencias ligadas aos municipios publicados.</p>
</section>

<section class="toolbar-card">
    <div class="toolbar-actions">
        <a class="button" href="<?= e(url('admin/turismo/novo')); ?>">Novo ponto turistico</a>
    </div>
</section>

<section class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Municipio</th>
                <th>Categoria</th>
                <th>Status</th>
                <th>Acoes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($points as $point): ?>
                <tr>
                    <td><?= e($point['nome']); ?></td>
                    <td><?= e($point['municipio_nome'] . ' (' . $point['estado_sigla'] . ')'); ?></td>
                    <td><?= e($point['categoria'] ?: '-'); ?></td>
                    <td><span class="badge"><?= e($point['status_curadoria']); ?></span></td>
                    <td>
                        <a href="<?= e(url('admin/turismo/' . $point['id_ponto_turistico'] . '/editar')); ?>">Editar</a>
                        <?php if ($point['status_curadoria'] === 'publicado'): ?>
                            <span class="divider-dot">|</span>
                            <a href="<?= e(url('municipio/' . $point['municipio_slug'])); ?>" target="_blank" rel="noopener noreferrer">Abrir municipio</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
