<section class="page-header">
    <p class="eyebrow">Automacao</p>
    <h1>Revisao da importacao automatica</h1>
    <p>Acompanhe as ultimas rodadas da descoberta em lote do Wikimedia Commons e veja rapidamente o que entrou, o que ficou pendente e onde houve erro.</p>
</section>

<section class="toolbar-card">
    <div class="toolbar-actions">
        <a class="button button-outline" href="<?= e(url('admin/midias')); ?>">Voltar para midias</a>
        <a class="button button-outline" href="<?= e(url('admin/midias/descobrir-lote')); ?>">Abrir descoberta em lote</a>
        <a class="button button-outline" href="<?= e(url('admin/midias/cobertura')); ?>">Ver cobertura visual</a>
    </div>
</section>

<?php if (!empty($error)): ?>
    <section class="toolbar-card">
        <p class="muted-line"><?= e((string) $error); ?></p>
    </section>
<?php elseif (($logs ?? []) === []): ?>
    <section class="toolbar-card">
        <p class="muted-line">Ainda nao existem logs dessa automacao. Execute uma importacao automatica em lote para começar a acompanhar o historico.</p>
    </section>
<?php else: ?>
    <section class="table-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Quando</th>
                    <th>Status</th>
                    <th>Categoria</th>
                    <th>Importadas</th>
                    <th>Puladas</th>
                    <th>Capas</th>
                    <th>Filtro</th>
                    <th>Detalhes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($logs ?? []) as $log): ?>
                    <?php
                    $params = $log['parametros_decodificados'] ?? [];
                    $summary = $log['resumo_decodificado'] ?? [];
                    $selectedCount = count($params['municipios'] ?? []);
                    $errors = $summary['erros'] ?? [];
                    ?>
                    <tr>
                        <td><?= e((string) ($log['data_execucao'] ?? '-')); ?></td>
                        <td><span class="badge"><?= e((string) ($log['status'] ?? '-')); ?></span></td>
                        <td><?= e((string) ($params['categoria'] ?? '-')); ?></td>
                        <td><?= (int) ($summary['importadas'] ?? 0); ?></td>
                        <td><?= (int) ($summary['puladas_sem_candidato'] ?? 0); ?></td>
                        <td><?= (int) ($summary['capas_preenchidas'] ?? 0); ?></td>
                        <td>
                            <?php if (!empty($params['estado'])): ?>
                                <span>Estado #<?= (int) $params['estado']; ?></span>
                            <?php else: ?>
                                <span>Todos os estados</span>
                            <?php endif; ?>
                            <span class="divider-dot">|</span>
                            <span>Limite: <?= (int) ($params['limite'] ?? 0); ?></span>
                            <span class="divider-dot">|</span>
                            <span>Selecionados: <?= $selectedCount; ?></span>
                        </td>
                        <td>
                            <div><?= e((string) ($log['mensagem'] ?? '-')); ?></div>
                            <?php if ($errors !== []): ?>
                                <div class="muted-line"><?= e(implode(' | ', array_slice($errors, 0, 2))); ?></div>
                            <?php else: ?>
                                <div class="muted-line">Sem erros registrados nessa rodada.</div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
<?php endif; ?>
