<section class="page-header">
    <p class="eyebrow">QR Code rastreavel</p>
    <h1><?= e($municipality['nome']); ?></h1>
    <p>Use este QR Code em materiais impressos, escolas, roteiros e pontos fisicos. Cada leitura passa pelo rastreamento do projeto antes de abrir a pagina publica.</p>
</section>

<section class="toolbar-card">
    <div class="hero-actions">
        <a class="button" href="<?= e($qrImageUrl); ?>" target="_blank" rel="noopener noreferrer">Abrir PNG</a>
        <button type="button" class="button button-outline" onclick="window.print()">Imprimir</button>
        <a class="button button-outline" href="<?= e(url('admin/municipios/' . $municipality['id_municipio'] . '/editar')); ?>">Voltar ao municipio</a>
    </div>
</section>

<section class="form-card" style="text-align:center">
    <img src="<?= e($qrImageUrl); ?>" alt="QR Code do municipio <?= e($municipality['nome']); ?>" style="max-width: 100%; width: 420px; height: auto;">
</section>

<section class="table-card">
    <table class="data-table">
        <tbody>
            <tr>
                <th>ID QR</th>
                <td><?= e((string) ($qrcode['id_qrcode'] ?? '-')); ?></td>
            </tr>
            <tr>
                <th>Status</th>
                <td><?= e((string) ($qrcode['status'] ?? 'ativo')); ?></td>
            </tr>
            <tr>
                <th>Total de acessos</th>
                <td><?= e((string) ($qrcode['total_acessos'] ?? 0)); ?></td>
            </tr>
            <tr>
                <th>URL rastreavel do QR</th>
                <td><code><?= e($trackingUrl); ?></code></td>
            </tr>
            <tr>
                <th>URL publica final</th>
                <td><code><?= e($publicUrl); ?></code></td>
            </tr>
        </tbody>
    </table>
</section>
