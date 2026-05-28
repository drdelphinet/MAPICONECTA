<section class="page-header">
    <p class="eyebrow">Galeria</p>
    <h1>Midias dos municipios</h1>
    <p>Gerencie imagens, videos, audios e documentos de apoio vinculados ao acervo territorial.</p>
</section>

<section class="toolbar-card">
    <div class="toolbar-actions">
        <a class="button" href="<?= e(url('admin/midias/nova')); ?>">Nova midia</a>
        <a class="button button-outline" href="<?= e(url('admin/midias/importar-wikimedia')); ?>">Importar do Wikimedia Commons</a>
        <a class="button button-outline" href="<?= e(url('admin/midias/importar-lote-wikimedia')); ?>">Importar lote</a>
        <a class="button button-outline" href="<?= e(url('admin/midias/descobrir')); ?>">Descoberta assistida</a>
        <a class="button button-outline" href="<?= e(url('admin/midias/descobrir-lote')); ?>">Descoberta em lote</a>
        <a class="button button-outline" href="<?= e(url('admin/midias/automacao')); ?>">Revisar automacao<?= !empty($automationLogsCount) ? ' (' . (int) $automationLogsCount . ')' : ''; ?></a>
        <a class="button button-outline" href="<?= e(url('admin/midias/cobertura')); ?>">Cobertura visual<?= !empty($coverageGapsCount) ? ' (' . (int) $coverageGapsCount . ')' : ''; ?></a>
    </div>
    <?php if ($mediaItems === []): ?>
        <p class="muted-line">Se esta lista estiver vazia logo apos o deploy, confirme se a tabela `midias_municipio` ja foi criada no banco.</p>
    <?php endif; ?>
</section>

<section class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Titulo</th>
                <th>Municipio</th>
                <th>Tipo</th>
                <th>Categoria</th>
                <th>Licenca</th>
                <th>Status</th>
                <th>Acoes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($mediaItems as $item): ?>
                <tr>
                    <td><?= e($item['titulo']); ?></td>
                    <td><?= e($item['municipio_nome'] . ' (' . $item['estado_sigla'] . ')'); ?></td>
                    <td><?= e($item['tipo']); ?></td>
                    <td><?= e($item['categoria_visual'] ?: '-'); ?></td>
                    <td><?= e($item['licenca'] ?: '-'); ?></td>
                    <td><span class="badge"><?= e($item['status_curadoria']); ?></span></td>
                    <td>
                        <a href="<?= e(url('admin/midias/' . $item['id_midia'] . '/editar')); ?>">Editar</a>
                        <?php if ($item['status_curadoria'] === 'publicado'): ?>
                            <span class="divider-dot">|</span>
                            <a href="<?= e(url('midia/' . $item['id_midia'])); ?>" target="_blank" rel="noopener noreferrer">Pagina publica</a>
                            <span class="divider-dot">|</span>
                            <a href="<?= e($item['url_arquivo']); ?>" target="_blank" rel="noopener noreferrer">Abrir arquivo</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
