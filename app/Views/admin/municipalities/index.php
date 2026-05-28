<section class="page-header">
    <p class="eyebrow">Administracao territorial</p>
    <h1>Municipios</h1>
    <p>Cadastre, edite, filtre e importe municipios do estado com base no IBGE.</p>
</section>

<section class="toolbar-card stack-form">
    <div class="toolbar-actions">
        <a class="button" href="<?= e(url('admin/municipios/novo')); ?>">Novo municipio</a>
    </div>

    <form method="get" action="<?= e(url('admin/municipios')); ?>" class="filter-grid">
        <label class="field">
            <span>Buscar</span>
            <input type="text" name="busca" value="<?= e($search); ?>" placeholder="Nome, slug ou codigo IBGE">
        </label>

        <label class="field">
            <span>Estado</span>
            <select name="estado_id">
                <option value="">Todos</option>
                <?php foreach ($states as $state): ?>
                    <option value="<?= (int) $state['id_estado']; ?>" <?= ($selectedStateId === (int) $state['id_estado']) ? 'selected' : ''; ?>>
                        <?= e($state['nome'] . ' (' . $state['sigla'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="field">
            <span>Coordenadas</span>
            <select name="coordenadas">
                <option value="">Todos</option>
                <option value="without" <?= (($selectedCoordinatesFilter ?? '') === 'without') ? 'selected' : ''; ?>>Sem coordenadas</option>
                <option value="with" <?= (($selectedCoordinatesFilter ?? '') === 'with') ? 'selected' : ''; ?>>Com coordenadas</option>
            </select>
        </label>

        <label class="field">
            <span>Publicacao</span>
            <select name="publicacao">
                <option value="">Todos</option>
                <option value="publicado" <?= (($selectedPublicationFilter ?? '') === 'publicado') ? 'selected' : ''; ?>>Publicado</option>
                <option value="rascunho" <?= (($selectedPublicationFilter ?? '') === 'rascunho') ? 'selected' : ''; ?>>Rascunho</option>
                <option value="despublicado" <?= (($selectedPublicationFilter ?? '') === 'despublicado') ? 'selected' : ''; ?>>Despublicado</option>
            </select>
        </label>

        <div class="toolbar-actions align-end">
            <button type="submit" class="button button-outline">Filtrar</button>
        </div>
    </form>

    <form method="post" action="<?= e(url('admin/municipios/importar-ibge')); ?>" class="filter-grid">
        <input type="hidden" name="_token" value="<?= e(\App\Core\Csrf::token()); ?>">
        <label class="field">
            <span>Importar do IBGE</span>
            <input type="text" name="estado_referencia" placeholder="Ex.: PI ou 22" required>
        </label>
        <div class="toolbar-actions align-end">
            <button type="submit" class="button">Importar municipios do estado</button>
        </div>
    </form>

    <form method="post" action="<?= e(url('admin/municipios/importar-dados-ibge')); ?>" class="filter-grid">
        <input type="hidden" name="_token" value="<?= e(\App\Core\Csrf::token()); ?>">
        <label class="field">
            <span>Dados factuais do IBGE</span>
            <input type="text" name="estado_dados" placeholder="Ex.: PI ou 22" required>
        </label>
        <div class="toolbar-actions align-end">
            <button type="submit" class="button button-outline">Importar populacao, area, densidade e gentilico</button>
        </div>
    </form>

    <form method="post" action="<?= e(url('admin/municipios/publicar-em-massa')); ?>" class="filter-grid">
        <input type="hidden" name="_token" value="<?= e(\App\Core\Csrf::token()); ?>">
        <label class="field">
            <span>Publicacao inicial em massa</span>
            <input type="text" name="estado_publicacao" placeholder="Ex.: PI ou 22" required>
        </label>
        <div class="toolbar-actions align-end">
            <button type="submit" class="button button-outline">Publicar municipios do estado</button>
        </div>
    </form>

    <form method="post" action="<?= e(url('admin/municipios/base-editorial-em-massa')); ?>" class="filter-grid">
        <input type="hidden" name="_token" value="<?= e(\App\Core\Csrf::token()); ?>">
        <label class="field">
            <span>Base editorial em massa</span>
            <input type="text" name="estado_editorial" placeholder="Ex.: PI ou 22" required>
        </label>
        <div class="toolbar-actions align-end">
            <button type="submit" class="button button-outline">Gerar base inicial do estado</button>
        </div>
    </form>

    <form method="post" action="<?= e(url('admin/midias/descobrir-lote/importar')); ?>" class="filter-grid">
        <input type="hidden" name="_token" value="<?= e(\App\Core\Csrf::token()); ?>">
        <input type="hidden" name="categoria" value="paisagem">
        <input type="hidden" name="limite" value="0">
        <input type="hidden" name="status_curadoria" value="rascunho">
        <input type="hidden" name="destaque" value="1">
        <input type="hidden" name="preencher_imagem_principal" value="1">
        <label class="field">
            <span>Wikimedia automatica</span>
            <select name="estado" required>
                <option value="">Selecione um estado publicado</option>
                <?php foreach ($states as $state): ?>
                    <option value="<?= (int) $state['id_estado']; ?>" <?= ($selectedStateId === (int) $state['id_estado']) ? 'selected' : ''; ?>>
                        <?= e($state['nome'] . ' (' . $state['sigla'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <div class="toolbar-actions align-end">
            <button type="submit" class="button button-outline">Importar Wikimedia do estado inteiro</button>
        </div>
        <p class="muted-line">Importa automaticamente a primeira imagem livre com boa confianca para todos os municipios publicados do estado que ainda estao sem cobertura visual.</p>
    </form>
</section>

<section class="toolbar-card admin-inline-summary">
    <span class="badge">Resultados: <?= (int) ($summary['total'] ?? 0); ?></span>
    <span class="badge">Oficiais: <?= (int) ($summary['official'] ?? 0); ?></span>
    <span class="badge">Publicados: <?= (int) ($summary['published'] ?? 0); ?></span>
    <span class="badge badge-warning">Faltando publicar: <?= (int) ($summary['missingPublication'] ?? 0); ?></span>
    <span class="badge">Com coordenadas: <?= (int) ($summary['withCoordinates'] ?? 0); ?></span>
    <span class="badge">Sem coordenadas: <?= (int) ($summary['withoutCoordinates'] ?? 0); ?></span>
    <span class="badge badge-warning">Fora da malha: <?= (int) ($summary['missingGeo'] ?? 0); ?></span>
    <span class="badge">Autorais: <?= (int) (($editorialSummary['autoral'] ?? 0)); ?></span>
    <span class="badge">Em curadoria: <?= (int) (($editorialSummary['em_curadoria'] ?? 0)); ?></span>
    <span class="badge badge-warning">Base estruturada: <?= (int) (($editorialSummary['base'] ?? 0)); ?></span>
    <span class="badge">Factual completo: <?= (int) (($factualSummary['completo'] ?? 0)); ?></span>
    <span class="badge">Factual parcial: <?= (int) (($factualSummary['parcial'] ?? 0)); ?></span>
    <span class="badge badge-warning">Factual vazio: <?= (int) (($factualSummary['vazio'] ?? 0)); ?></span>
</section>

<?php if (($missingGeoMunicipalities ?? []) !== []): ?>
    <section class="toolbar-card">
        <h2>Publicado(s) fora da malha territorial</h2>
        <p class="muted-line">Estes municipios ja estao publicados, mas ainda nao aparecem com contorno territorial no GeoJSON atual.</p>
        <div class="admin-link-list">
            <?php foreach (($missingGeoMunicipalities ?? []) as $municipality): ?>
                <a href="<?= e(url('admin/municipios/' . $municipality['id_municipio'] . '/editar')); ?>">
                    <strong><?= e($municipality['nome']); ?></strong>
                    <span><?= e($municipality['estado_sigla'] . ' - codigo IBGE ' . $municipality['codigo_ibge']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<?php if (($priorityMunicipalities ?? []) !== []): ?>
    <section class="toolbar-card">
        <h2>Fila editorial recomendada</h2>
        <p class="muted-line">Estes municipios ainda estao com menor cobertura editorial e sao os melhores candidatos para a proxima rodada de enriquecimento.</p>
        <div class="admin-link-list">
            <?php foreach (($priorityMunicipalities ?? []) as $municipality): ?>
                <a href="<?= e(url('admin/municipios/' . $municipality['id_municipio'] . '/editar')); ?>">
                    <strong><?= e($municipality['nome']); ?></strong>
                    <span>
                        <?= e((string) ($municipality['editorial_authored_score'] ?? 0)); ?>% autoral
                        <?php if (($municipality['editorial_missing_sections'] ?? []) !== []): ?>
                            - faltando: <?= e(implode(', ', array_slice($municipality['editorial_missing_sections'], 0, 3))); ?>
                        <?php endif; ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<section class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Municipio</th>
                <th>Estado</th>
                <th>Codigo IBGE</th>
                <th>Mapa</th>
                <th>Conteudo</th>
                <th>Dados factuais</th>
                <th>Criado por</th>
                <th>Revisado por</th>
                <th>Curadoria</th>
                <th>Publicacao</th>
                <th>Acoes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($municipalities as $municipality): ?>
                <tr>
                    <td><?= e($municipality['nome']); ?></td>
                    <td><?= e($municipality['estado_sigla']); ?></td>
                    <td><?= e((string) $municipality['codigo_ibge']); ?></td>
                    <td>
                        <?php if ($municipality['latitude'] !== null && $municipality['longitude'] !== null): ?>
                            <span class="badge">Com coordenadas</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Sem coordenadas</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge editorial-badge editorial-badge-<?= e((string) ($municipality['editorial_status'] ?? 'essencial')); ?>">
                            <?= e((string) ($municipality['editorial_authored_score'] ?? 0)); ?>%
                        </span>
                    </td>
                    <td>
                        <span class="badge"><?= e((string) ($municipality['facts_score'] ?? 0)); ?>%</span>
                    </td>
                    <td><?= e($municipality['criado_por_nome'] ?? '-'); ?></td>
                    <td><?= e($municipality['revisado_por_nome'] ?? '-'); ?></td>
                    <td><span class="badge"><?= e($municipality['status_curadoria']); ?></span></td>
                    <td><span class="badge"><?= e($municipality['status_publicacao']); ?></span></td>
                    <td>
                        <a href="<?= e(url('admin/municipios/' . $municipality['id_municipio'] . '/editar')); ?>">Editar</a>
                        <span class="divider-dot">-</span>
                        <a href="<?= e(url('admin/municipios/' . $municipality['id_municipio'] . '/qrcode')); ?>">QR Code</a>
                        <span class="divider-dot">-</span>
                        <a href="<?= e(url('admin/midias/descobrir?municipio=' . $municipality['slug'] . '&categoria=paisagem')); ?>">Wikimedia</a>
                        <?php if ($municipality['status_publicacao'] === 'publicado'): ?>
                            <span class="divider-dot">-</span>
                            <a href="<?= e(url('municipio/' . $municipality['slug'])); ?>" target="_blank" rel="noopener noreferrer">Ver publico</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
