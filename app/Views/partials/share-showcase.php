<?php
$shareEyebrow = $shareEyebrow ?? 'Compartilhamento';
$shareTitle = $shareTitle ?? 'Leve este conteudo para outras telas';
$shareDescription = $shareDescription ?? 'Compartilhe esta pagina em redes sociais, copie o link direto ou use o QR Code para materiais impressos.';
$shareEntityTitle = $shareEntityTitle ?? ($title ?? 'MAPI CONECTA');
$sharePublicUrl = $sharePublicUrl ?? '';
$shareQrImageUrl = $shareQrImageUrl ?? null;
$shareQrTrackingUrl = $shareQrTrackingUrl ?? null;
$shareQrTitle = $shareQrTitle ?? 'Entrada rapida por leitura';
$shareQrDescription = $shareQrDescription ?? 'Use este QR para abrir o conteudo pelo link rastreavel do projeto.';
?>
<section class="container share-showcase">
    <article class="panel-card share-showcase-card">
        <p class="eyebrow"><?= e($shareEyebrow); ?></p>
        <h2><?= e($shareTitle); ?></h2>
        <p><?= e($shareDescription); ?></p>

        <div class="share-card-shell" data-share-card data-title="<?= e($shareEntityTitle); ?>" data-url="<?= e($sharePublicUrl); ?>">
            <div class="share-card-main">
                <p class="share-card-link"><code><?= e($sharePublicUrl); ?></code></p>
                <div class="share-card-actions">
                    <button type="button" class="button" data-share-native>Compartilhar agora</button>
                    <button type="button" class="button button-outline" data-copy-link>Copiar link</button>
                </div>
                <div class="share-network-grid">
                    <a class="share-network-chip" href="https://wa.me/?text=<?= e(rawurlencode('Conheca ' . $shareEntityTitle . ' no MAPI CONECTA: ' . $sharePublicUrl)); ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a>
                    <a class="share-network-chip" href="https://www.facebook.com/sharer/sharer.php?u=<?= e(rawurlencode($sharePublicUrl)); ?>" target="_blank" rel="noopener noreferrer">Facebook</a>
                    <a class="share-network-chip" href="https://twitter.com/intent/tweet?text=<?= e(rawurlencode('Conheca ' . $shareEntityTitle . ' no MAPI CONECTA')); ?>&url=<?= e(rawurlencode($sharePublicUrl)); ?>" target="_blank" rel="noopener noreferrer">X</a>
                    <a class="share-network-chip" href="https://t.me/share/url?url=<?= e(rawurlencode($sharePublicUrl)); ?>&text=<?= e(rawurlencode('Conheca ' . $shareEntityTitle . ' no MAPI CONECTA')); ?>" target="_blank" rel="noopener noreferrer">Telegram</a>
                    <a class="share-network-chip" href="mailto:?subject=<?= e(rawurlencode('MAPI CONECTA | ' . $shareEntityTitle)); ?>&body=<?= e(rawurlencode('Confira esta pagina: ' . $sharePublicUrl)); ?>">E-mail</a>
                </div>
                <p class="muted-line" data-copy-feedback hidden>Link copiado para a area de transferencia.</p>
            </div>

            <?php if ($shareQrImageUrl !== null && $shareQrTrackingUrl !== null): ?>
                <aside class="share-card-qr">
                    <p class="eyebrow">QR Code</p>
                    <h3><?= e($shareQrTitle); ?></h3>
                    <p><?= e($shareQrDescription); ?></p>
                    <img src="<?= e($shareQrImageUrl); ?>" alt="QR Code de <?= e($shareEntityTitle); ?>" class="share-qr-image">
                    <a class="button button-soft" href="<?= e($shareQrImageUrl); ?>" target="_blank" rel="noopener noreferrer">Abrir QR em tamanho maior</a>
                    <p class="share-card-link"><code><?= e($shareQrTrackingUrl); ?></code></p>
                </aside>
            <?php endif; ?>
        </div>
    </article>
</section>
