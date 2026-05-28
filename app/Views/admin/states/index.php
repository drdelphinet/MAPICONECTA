<section class="page-header">
    <p class="eyebrow">Administracao territorial</p>
    <h1>Estados</h1>
    <p>Gerencie estados cadastrados e sincronize a base inicial com a API oficial de Localidades do IBGE.</p>
</section>

<section class="toolbar-card">
    <div class="toolbar-actions">
        <a class="button" href="<?= e(url('admin/estados/novo')); ?>">Novo estado</a>
        <form method="post" action="<?= e(url('admin/estados/importar-ibge')); ?>" class="toolbar-inline-form">
            <input type="hidden" name="_token" value="<?= e(\App\Core\Csrf::token()); ?>">
            <button type="submit" class="button button-outline">Importar estados do IBGE</button>
        </form>
    </div>
</section>

<section class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Sigla</th>
                <th>Codigo IBGE</th>
                <th>Regiao</th>
                <th>Status</th>
                <th>Acoes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($states as $state): ?>
                <tr>
                    <td><?= e($state['nome']); ?></td>
                    <td><?= e($state['sigla']); ?></td>
                    <td><?= e((string) $state['codigo_ibge']); ?></td>
                    <td><?= e($state['regiao']); ?></td>
                    <td><span class="badge"><?= e($state['status']); ?></span></td>
                    <td><a href="<?= e(url('admin/estados/' . $state['id_estado'] . '/editar')); ?>">Editar</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
