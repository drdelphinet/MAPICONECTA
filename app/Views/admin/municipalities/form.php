<section class="page-header">
    <p class="eyebrow">Administracao territorial</p>
    <h1><?= $municipality ? 'Editar municipio' : 'Novo municipio'; ?></h1>
    <p>Formulario inicial para cadastro editorial, dados basicos e status de curadoria/publicacao.</p>
</section>

<?php if ($municipality): ?>
    <section class="toolbar-card">
        <div class="editorial-meter">
            <div>
                <strong>Prontidao editorial: <?= (int) ($municipality['editorial_authored_score'] ?? 0); ?>%</strong>
                <p>
                    <?= (int) ($municipality['editorial_authored_sections'] ?? 0); ?> de <?= (int) ($municipality['editorial_total_sections'] ?? 0); ?>
                    secoes ja estao com leitura mais autoral. A cobertura estrutural total continua em <?= (int) ($municipality['editorial_score'] ?? 0); ?>%.
                </p>
            </div>
            <span class="badge editorial-badge editorial-badge-<?= e((string) ($municipality['editorial_status'] ?? 'essencial')); ?>">
                <?= e(str_replace('_', ' ', (string) ($municipality['editorial_status'] ?? 'essencial'))); ?>
            </span>
        </div>

        <?php if (($municipality['editorial_missing_sections'] ?? []) !== []): ?>
            <div class="story-band-list admin-checklist">
                <?php foreach (($municipality['editorial_missing_sections'] ?? []) as $section): ?>
                    <span class="story-pill"><?= e($section); ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>

<section class="form-card">
    <?php $role = $user['role_slug'] ?? ''; ?>

    <form method="post" action="<?= e($formAction); ?>" class="stack-form">
        <input type="hidden" name="_token" value="<?= e(\App\Core\Csrf::token()); ?>">

        <div class="form-grid">
            <label class="field">
                <span>Estado</span>
                <select name="id_estado" required>
                    <option value="">Selecione</option>
                    <?php foreach ($states as $state): ?>
                        <option value="<?= (int) $state['id_estado']; ?>" <?= ((int) ($municipality['id_estado'] ?? 0) === (int) $state['id_estado']) ? 'selected' : ''; ?>>
                            <?= e($state['nome'] . ' (' . $state['sigla'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="field">
                <span>Codigo IBGE</span>
                <input type="number" name="codigo_ibge" value="<?= e((string) ($municipality['codigo_ibge'] ?? '')); ?>" required>
            </label>

            <label class="field">
                <span>Nome</span>
                <input type="text" name="nome" value="<?= e((string) ($municipality['nome'] ?? '')); ?>" required>
            </label>

            <label class="field">
                <span>Slug</span>
                <input type="text" name="slug" value="<?= e((string) ($municipality['slug'] ?? '')); ?>" placeholder="gerado automaticamente se vazio">
            </label>

            <label class="field field-full">
                <span>Descricao curta</span>
                <textarea name="descricao_curta" rows="3"><?= e((string) ($municipality['descricao_curta'] ?? '')); ?></textarea>
            </label>

            <label class="field">
                <span>Populacao</span>
                <input type="number" name="populacao" value="<?= e((string) ($municipality['populacao'] ?? '')); ?>">
            </label>

            <label class="field">
                <span>Area (km²)</span>
                <input type="number" step="0.01" name="area" value="<?= e((string) ($municipality['area'] ?? '')); ?>">
            </label>

            <label class="field">
                <span>Densidade demografica</span>
                <input type="number" step="0.01" name="densidade_demografica" value="<?= e((string) ($municipality['densidade_demografica'] ?? '')); ?>">
            </label>

            <label class="field">
                <span>Gentilico</span>
                <input type="text" name="gentilico" value="<?= e((string) ($municipality['gentilico'] ?? '')); ?>">
            </label>

            <label class="field">
                <span>Data de fundacao</span>
                <input type="date" name="data_fundacao" value="<?= e((string) ($municipality['data_fundacao'] ?? '')); ?>">
            </label>

            <label class="field">
                <span>Latitude</span>
                <input type="number" step="0.0000001" name="latitude" value="<?= e((string) ($municipality['latitude'] ?? '')); ?>">
            </label>

            <label class="field">
                <span>Longitude</span>
                <input type="number" step="0.0000001" name="longitude" value="<?= e((string) ($municipality['longitude'] ?? '')); ?>">
            </label>

            <label class="field">
                <span>Distancia da capital</span>
                <input type="number" step="0.01" name="distancia_capital" value="<?= e((string) ($municipality['distancia_capital'] ?? '')); ?>">
            </label>

            <label class="field">
                <span>Imagem principal</span>
                <input type="text" name="imagem_principal" value="<?= e((string) ($municipality['imagem_principal'] ?? '')); ?>" placeholder="https://...">
            </label>

            <label class="field">
                <span>Fonte dos dados</span>
                <input type="text" name="fonte_dados" value="<?= e((string) ($municipality['fonte_dados'] ?? '')); ?>" placeholder="Ex.: IBGE Localidades">
            </label>

            <label class="field">
                <span>Status da curadoria</span>
                <select name="status_curadoria">
                    <?php foreach (['rascunho', 'enviado_revisao', 'em_correcao', 'aprovado', 'publicado', 'reprovado', 'arquivado'] as $status): ?>
                        <option value="<?= e($status); ?>" <?= (($municipality['status_curadoria'] ?? 'rascunho') === $status) ? 'selected' : ''; ?>><?= e($status); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="field">
                <span>Status da publicacao</span>
                <select name="status_publicacao">
                    <?php foreach (['rascunho', 'publicado', 'despublicado'] as $status): ?>
                        <option value="<?= e($status); ?>" <?= (($municipality['status_publicacao'] ?? 'rascunho') === $status) ? 'selected' : ''; ?>><?= e($status); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="field field-full">
                <span>Historia</span>
                <textarea name="historia" rows="5"><?= e((string) ($municipality['historia'] ?? '')); ?></textarea>
            </label>

            <label class="field field-full">
                <span>Geografia</span>
                <textarea name="geografia" rows="5"><?= e((string) ($municipality['geografia'] ?? '')); ?></textarea>
            </label>

            <label class="field field-full">
                <span>Economia</span>
                <textarea name="economia" rows="5"><?= e((string) ($municipality['economia'] ?? '')); ?></textarea>
            </label>

            <label class="field field-full">
                <span>Cultura</span>
                <textarea name="cultura" rows="5"><?= e((string) ($municipality['cultura'] ?? '')); ?></textarea>
            </label>

            <label class="field field-full">
                <span>Turismo</span>
                <textarea name="turismo" rows="5"><?= e((string) ($municipality['turismo'] ?? '')); ?></textarea>
            </label>

            <label class="field field-full">
                <span>Educacao</span>
                <textarea name="educacao" rows="5"><?= e((string) ($municipality['educacao'] ?? '')); ?></textarea>
            </label>

            <label class="field field-full">
                <span>Curiosidades</span>
                <textarea name="curiosidades" rows="5"><?= e((string) ($municipality['curiosidades'] ?? '')); ?></textarea>
            </label>
        </div>

        <div class="toolbar-actions">
            <button type="submit" class="button">Salvar</button>
            <?php if ($municipality): ?>
                <button type="submit" formaction="<?= e(url('admin/municipios/' . $municipality['id_municipio'] . '/gerar-base-editorial')); ?>" class="button button-outline">Gerar base editorial</button>
                <a class="button button-outline" href="<?= e(url('admin/municipios/' . $municipality['id_municipio'] . '/qrcode')); ?>">Abrir QR Code</a>
                <a class="button button-outline" href="<?= e(url('admin/curadoria')); ?>">Fila de curadoria</a>
            <?php endif; ?>
            <a class="button button-outline" href="<?= e(url('admin/municipios')); ?>">Voltar</a>
        </div>
    </form>
</section>

<?php if ($municipality): ?>
    <section class="toolbar-card">
        <h2>Acoes de curadoria</h2>
        <p class="muted-line">Perfil atual: <?= e($user['role_name'] ?? '-'); ?>. Status atual: <strong><?= e($municipality['status_curadoria']); ?></strong>.</p>

        <?php if (($municipality['status_publicacao'] ?? '') === 'publicado'): ?>
            <p class="muted-line">Este municipio ja pode circular com QR Code rastreavel para materiais impressos e experiencias presenciais.</p>
        <?php endif; ?>

        <form method="post" action="<?= e(url('admin/municipios/' . $municipality['id_municipio'] . '/workflow/' . ($role === 'editor' ? 'enviar-revisao' : 'aprovar'))); ?>" class="stack-form">
            <input type="hidden" name="_token" value="<?= e(\App\Core\Csrf::token()); ?>">
            <label class="field">
                <span>Observacao interna</span>
                <textarea name="observacao_curadoria" rows="3" placeholder="Adicione um contexto curto para o historico de curadoria"></textarea>
            </label>

            <div class="workflow-actions">
                <?php if (in_array($role, ['editor', 'administrador-geral'], true)): ?>
                    <button type="submit" formaction="<?= e(url('admin/municipios/' . $municipality['id_municipio'] . '/workflow/enviar-revisao')); ?>" class="button button-outline">Enviar para revisao</button>
                <?php endif; ?>

                <?php if (in_array($role, ['curador', 'administrador-geral'], true)): ?>
                    <button type="submit" formaction="<?= e(url('admin/municipios/' . $municipality['id_municipio'] . '/workflow/em-correcao')); ?>" class="button button-outline">Solicitar correcao</button>
                    <button type="submit" formaction="<?= e(url('admin/municipios/' . $municipality['id_municipio'] . '/workflow/aprovar')); ?>" class="button">Aprovar</button>
                    <button type="submit" formaction="<?= e(url('admin/municipios/' . $municipality['id_municipio'] . '/workflow/reprovar')); ?>" class="button button-outline">Reprovar</button>
                <?php endif; ?>

                <?php if ($role === 'administrador-geral'): ?>
                    <button type="submit" formaction="<?= e(url('admin/municipios/' . $municipality['id_municipio'] . '/workflow/publicar')); ?>" class="button">Publicar</button>
                    <button type="submit" formaction="<?= e(url('admin/municipios/' . $municipality['id_municipio'] . '/workflow/arquivar')); ?>" class="button button-outline">Arquivar</button>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <section class="table-card">
        <h2>Historico de curadoria</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Quando</th>
                    <th>Usuario</th>
                    <th>Acao</th>
                    <th>De</th>
                    <th>Para</th>
                    <th>Observacao</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($history ?? []) as $entry): ?>
                    <tr>
                        <td><?= e((string) $entry['criado_em']); ?></td>
                        <td><?= e($entry['usuario_nome'] ?? '-'); ?></td>
                        <td><?= e($entry['acao']); ?></td>
                        <td><?= e((string) ($entry['status_anterior'] ?? '-')); ?></td>
                        <td><?= e((string) ($entry['status_novo'] ?? '-')); ?></td>
                        <td><?= e((string) ($entry['observacao'] ?? '-')); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
<?php endif; ?>
