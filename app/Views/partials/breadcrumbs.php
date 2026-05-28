<?php if (($breadcrumbs ?? []) !== []): ?>
    <nav class="container breadcrumb-trail" aria-label="Breadcrumb">
        <ol class="breadcrumb-list">
            <?php foreach ($breadcrumbs as $index => $breadcrumb): ?>
                <?php $isLast = $index === array_key_last($breadcrumbs); ?>
                <li class="breadcrumb-item">
                    <?php if (!$isLast && !empty($breadcrumb['url'])): ?>
                        <a href="<?= e((string) $breadcrumb['url']); ?>"><?= e((string) $breadcrumb['label']); ?></a>
                    <?php else: ?>
                        <span aria-current="<?= $isLast ? 'page' : 'false'; ?>"><?= e((string) $breadcrumb['label']); ?></span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    </nav>
<?php endif; ?>
