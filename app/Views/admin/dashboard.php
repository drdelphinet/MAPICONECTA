<section class="page-header">
    <p class="eyebrow">Painel administrativo</p>
    <h1>Dashboard geral</h1>
    <p>Operacao central do MAPI CONECTA para curadoria, importacoes, crescimento do acervo e acompanhamento da plataforma.</p>
</section>

<section class="admin-dashboard-hero">
    <article class="panel-card admin-dashboard-main">
        <p class="eyebrow">Painel forte</p>
        <h2>O backend existe para sustentar publicacao confiavel, nao apenas cadastro.</h2>
        <p>Estados, municipios, quizzes e curadoria precisam conversar entre si para que a area publica cresca com consistencia e seguranca editorial.</p>
    </article>

    <article class="panel-card admin-dashboard-side">
        <p class="eyebrow">Acoes rapidas</p>
        <div class="admin-quick-links">
            <a href="<?= e(url('admin/municipios')); ?>">Gerenciar municipios</a>
            <a href="<?= e(url('admin/turismo')); ?>">Gerenciar turismo</a>
            <a href="<?= e(url('admin/midias')); ?>">Gerenciar midias</a>
            <a href="<?= e(url('admin/quizzes')); ?>">Gerenciar quizzes</a>
            <a href="<?= e(url('admin/curadoria')); ?>">Abrir curadoria</a>
            <a href="<?= e(url('admin/estados')); ?>">Ver estados</a>
        </div>
    </article>
</section>

<section class="stats-grid">
    <article class="stat-card"><span>Tabelas do banco</span><strong><?= (int) $stats['tabelas_banco']; ?></strong></article>
    <article class="stat-card"><span>Estados cadastrados</span><strong><?= (int) $stats['estados']; ?></strong></article>
    <article class="stat-card"><span>Municipios oficiais</span><strong><?= (int) $stats['municipios_oficiais']; ?></strong></article>
    <article class="stat-card"><span>Municipios cadastrados</span><strong><?= (int) $stats['municipios']; ?></strong></article>
    <article class="stat-card"><span>Municipios publicados</span><strong><?= (int) $stats['municipios_publicados']; ?></strong></article>
    <article class="stat-card"><span>Com coordenadas</span><strong><?= (int) $stats['municipios_com_coordenadas']; ?></strong></article>
    <article class="stat-card"><span>Sem coordenadas</span><strong><?= (int) $stats['municipios_sem_coordenadas']; ?></strong></article>
    <article class="stat-card"><span>Cobertura do mapa</span><strong><?= (int) $stats['cobertura_mapa_percentual']; ?>%</strong></article>
    <article class="stat-card"><span>Cobertura oficial</span><strong><?= (int) $stats['cobertura_mapa_oficial_percentual']; ?>%</strong></article>
    <article class="stat-card"><span>Municipios pendentes</span><strong><?= (int) $stats['municipios_pendentes']; ?></strong></article>
    <article class="stat-card"><span>Pontos turisticos</span><strong><?= (int) $stats['pontos_turisticos']; ?></strong></article>
    <article class="stat-card"><span>Midias</span><strong><?= (int) $stats['midias_municipio']; ?></strong></article>
    <article class="stat-card"><span>Quizzes</span><strong><?= (int) $stats['quizzes']; ?></strong></article>
    <article class="stat-card"><span>Perguntas de quiz</span><strong><?= (int) $stats['perguntas_quiz']; ?></strong></article>
    <article class="stat-card"><span>Atividades pedagogicas</span><strong><?= (int) $stats['atividades_pedagogicas']; ?></strong></article>
    <article class="stat-card"><span>Usuarios</span><strong><?= (int) $stats['usuarios']; ?></strong></article>
    <article class="stat-card"><span>Conteudos pendentes</span><strong><?= (int) $stats['conteudos_pendentes']; ?></strong></article>
</section>

<section class="admin-grid-3">
    <article class="panel-card">
        <p class="eyebrow">Visao executiva</p>
        <h2>O banco coleta seis camadas de informacao que sustentam todo o projeto.</h2>
        <p>O desenho atual combina identidade de usuario, base territorial, curadoria, educacao, quizzes e inteligencia operacional em torno do municipio como unidade principal.</p>
    </article>

    <article class="panel-card warm-panel">
        <p class="eyebrow">Coleta viva</p>
        <h2>Interacoes ja rastreadas no produto</h2>
        <div class="workflow-pill-row">
            <span class="story-pill">Favoritos: <?= (int) $stats['favoritos']; ?></span>
            <span class="story-pill">Visitados: <?= (int) $stats['visitados']; ?></span>
            <span class="story-pill">Respostas: <?= (int) $stats['respostas_quiz']; ?></span>
            <span class="story-pill">Progresso salvo: <?= (int) $stats['progresso_quiz']; ?></span>
        </div>
    </article>

    <article class="panel-card">
        <p class="eyebrow">Operacao</p>
        <h2>Rastreabilidade tecnica e distribuicao</h2>
        <div class="workflow-pill-row">
            <span class="story-pill">QR Codes: <?= (int) $stats['qrcodes']; ?></span>
            <span class="story-pill">Acessos QR: <?= (int) $stats['qrcode_acessos']; ?></span>
            <span class="story-pill">Logs de integracao: <?= (int) $stats['logs_integracao']; ?></span>
            <span class="story-pill">Historico curadoria: <?= (int) $stats['historico_curadoria']; ?></span>
        </div>
    </article>
</section>

<?php if (($missingGeoMunicipalities ?? []) !== []): ?>
    <section class="admin-grid-2">
        <article class="panel-card warning-panel">
            <p class="eyebrow">Malha territorial</p>
            <h2>Municipios publicados fora do arquivo atual</h2>
            <p>O acervo publico esta quase todo georreferenciado, mas estes casos ainda nao aparecem com contorno territorial enquanto a malha nao for atualizada.</p>
            <div class="admin-link-list">
                <?php foreach (($missingGeoMunicipalities ?? []) as $municipality): ?>
                    <a href="<?= e(url('admin/municipios/' . $municipality['id_municipio'] . '/editar')); ?>">
                        <strong><?= e($municipality['nome']); ?></strong>
                        <span><?= e($municipality['estado_sigla'] . ' • codigo IBGE ' . $municipality['codigo_ibge']); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="panel-card">
            <p class="eyebrow">Leitura operacional</p>
            <h2>O gargalo do mapa deixou de ser coordenada e virou qualidade da malha.</h2>
            <p>Com <?= (int) $stats['cobertura_mapa_percentual']; ?>% do acervo publicado e <?= (int) $stats['cobertura_mapa_oficial_percentual']; ?>% do total oficial ja marcados, o proximo ajuste passa a ser manter o arquivo territorial sincronizado com a base do IBGE.</p>
        </article>
    </section>
<?php endif; ?>

<section class="admin-grid-3">
    <article class="panel-card">
        <p class="eyebrow">Perfis administrativos</p>
        <h2>Responsabilidades separadas para editar, revisar e publicar.</h2>
        <ul class="feature-list">
            <li>Administrador Geral controla o sistema.</li>
            <li>Curador revisa qualidade e adequacao do conteudo.</li>
            <li>Editor alimenta o acervo e envia para aprovacao.</li>
        </ul>
    </article>

    <article class="panel-card">
        <p class="eyebrow">Fluxo de curadoria</p>
        <h2>Do rascunho ate a publicacao.</h2>
        <div class="workflow-pill-row">
            <span class="story-pill">Rascunho</span>
            <span class="story-pill">Revisao</span>
            <span class="story-pill">Correcao</span>
            <span class="story-pill">Aprovado</span>
            <span class="story-pill">Publicado</span>
        </div>
    </article>

    <article class="panel-card warm-panel">
        <p class="eyebrow">Estado atual</p>
        <h2>A fundacao tecnica ja permite operar o crescimento do produto.</h2>
        <p>O proximo ganho vem de enriquecer integracoes publicas, importacoes e uma visao editorial mais completa sobre cada municipio.</p>
    </article>
</section>

<section class="admin-grid-2">
    <article class="panel-card">
        <p class="eyebrow">Modulos previstos</p>
        <ul class="feature-list">
            <li>Estados e municipios</li>
            <li>Curadoria</li>
            <li>Importacoes e APIs publicas</li>
            <li>QR Codes, midias e quizzes</li>
            <li>Pontos turisticos e acervo visual</li>
            <li>Relatorios e integracoes externas</li>
        </ul>
    </article>

    <article class="panel-card">
        <p class="eyebrow">Leitura do painel</p>
        <p>Municipios publicados medem o que ja chegou a area publica. Pendencias e conteudos pendentes medem o que ainda depende de fluxo editorial.</p>
    </article>
</section>

<section class="admin-grid-2">
    <article class="panel-card">
        <p class="eyebrow">Turismo recente</p>
        <h2>Atrativos em movimento</h2>
        <div class="admin-link-list">
            <?php foreach (($recentPoints ?? []) as $point): ?>
                <a href="<?= e(url('admin/turismo/' . $point['id_ponto_turistico'] . '/editar')); ?>">
                    <strong><?= e($point['nome']); ?></strong>
                    <span><?= e($point['municipio_nome'] . ' • ' . $point['status_curadoria']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </article>

    <article class="panel-card">
        <p class="eyebrow">Midias recentes</p>
        <h2>Galeria em movimento</h2>
        <div class="admin-link-list">
            <?php foreach (($recentMedia ?? []) as $item): ?>
                <a href="<?= e(url('admin/midias/' . $item['id_midia'] . '/editar')); ?>">
                    <strong><?= e($item['titulo']); ?></strong>
                    <span><?= e($item['municipio_nome'] . ' • ' . $item['tipo'] . ' • ' . $item['status_curadoria']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </article>
</section>

<section class="section-heading">
    <p class="eyebrow">Mapa do banco</p>
    <h2>O que o sistema coleta em banco de dados</h2>
</section>

<section class="admin-domain-grid">
    <?php foreach (($dataDomains ?? []) as $domain): ?>
        <article class="panel-card domain-card">
            <p class="eyebrow">Dominio de dados</p>
            <h2><?= e($domain['title']); ?></h2>
            <p><?= e($domain['description']); ?></p>

            <div class="domain-chip-group">
                <?php foreach (($domain['tables'] ?? []) as $table): ?>
                    <span class="domain-chip"><?= e($table); ?></span>
                <?php endforeach; ?>
            </div>

            <ul class="feature-list">
                <?php foreach (($domain['captures'] ?? []) as $capture): ?>
                    <li><?= e($capture); ?></li>
                <?php endforeach; ?>
            </ul>
        </article>
    <?php endforeach; ?>
</section>

<section class="story-band">
    <div>
        <p class="eyebrow">Leitura para apresentacao</p>
        <h2>O municipio e o eixo principal que conecta dado bruto, conteudo curado e experiencia do usuario.</h2>
        <p>Quase tudo converge para o cadastro municipal: a base geoterritorial entra, passa por curadoria, ganha camadas educativas e turisticas, vira quiz, QR Code e depois retorna como sinais de uso e interesse.</p>
    </div>

    <div class="story-band-list">
        <span class="story-pill">Territorio</span>
        <span class="story-pill">Curadoria</span>
        <span class="story-pill">Educacao</span>
        <span class="story-pill">Turismo</span>
        <span class="story-pill">Gamificacao</span>
        <span class="story-pill">Rastreabilidade</span>
    </div>
</section>

<section class="section-heading">
    <p class="eyebrow">Fluxo do projeto</p>
    <h2>Como a informacao percorre o sistema</h2>
</section>

<section class="journey-section">
    <div class="journey-grid journey-grid-6">
        <?php foreach (($projectFlow ?? []) as $flow): ?>
            <article class="panel-card journey-card">
                <span class="journey-step"><?= e($flow['step']); ?></span>
                <h2><?= e($flow['title']); ?></h2>
                <p><?= e($flow['description']); ?></p>
                <div class="workflow-pill-row">
                    <?php foreach (($flow['outputs'] ?? []) as $output): ?>
                        <span class="story-pill"><?= e($output); ?></span>
                    <?php endforeach; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="admin-grid-2">
    <article class="panel-card">
        <p class="eyebrow">Fontes de coleta</p>
        <h2>De onde os dados entram</h2>
        <div class="admin-link-list admin-static-list">
            <?php foreach (($collectionSources ?? []) as $source): ?>
                <div>
                    <strong><?= e($source['title']); ?></strong>
                    <span><?= e($source['description']); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </article>

    <article class="panel-card warm-panel">
        <p class="eyebrow">Resumo narrativo</p>
        <h2>Como explicar o MAPI CONECTA em uma reuniao</h2>
        <p>O projeto coleta dados territoriais, editoriais, educacionais e de interacao para transformar cada municipio em uma experiencia digital. O banco nao guarda apenas cadastro: ele sustenta fluxo de curadoria, publicacao, engajamento e leitura gerencial da plataforma.</p>
        <p>Se voce quiser exportar isso para proposta, reuniao ou patrocinio, o conteudo deste painel tambem pode ser convertido em PDF usando impressao do navegador a partir da area administrativa.</p>
    </article>
</section>

<section class="admin-grid-3">
    <article class="panel-card">
        <p class="eyebrow">Fila editorial</p>
        <h2>Resumo do workflow</h2>
        <div class="workflow-pill-row">
            <?php foreach (($workflowSummary ?? []) as $status => $total): ?>
                <span class="story-pill"><?= e($status); ?>: <?= (int) $total; ?></span>
            <?php endforeach; ?>
        </div>
    </article>

    <article class="panel-card">
        <p class="eyebrow">Municipios recentes</p>
        <h2>Ultimas movimentacoes</h2>
        <div class="admin-link-list">
            <?php foreach (($recentMunicipalities ?? []) as $municipality): ?>
                <a href="<?= e(url('admin/municipios/' . $municipality['id_municipio'] . '/editar')); ?>">
                    <strong><?= e($municipality['nome']); ?></strong>
                    <span><?= e($municipality['estado_sigla'] . ' • ' . $municipality['status_curadoria']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </article>

    <article class="panel-card warm-panel">
        <p class="eyebrow">Acao recomendada</p>
        <h2>Use a curadoria para destravar publicacoes.</h2>
        <p>Quando a fila editorial anda, a home, o mapa e o MAPI Educacao passam a ganhar profundidade imediatamente.</p>
        <div class="hero-actions">
            <a class="button" href="<?= e(url('admin/curadoria')); ?>">Abrir curadoria</a>
            <a class="button button-outline" href="<?= e(url('admin/municipios')); ?>">Ver municipios</a>
        </div>
    </article>
</section>

<section class="admin-grid-2">
    <article class="panel-card">
        <p class="eyebrow">Quizzes recentes</p>
        <h2>Camada de engajamento</h2>
        <div class="admin-link-list">
            <?php foreach (($recentQuizzes ?? []) as $quiz): ?>
                <a href="<?= e(url('admin/quizzes/' . $quiz['id_quiz'] . '/editar')); ?>">
                    <strong><?= e($quiz['titulo']); ?></strong>
                    <span><?= e($quiz['municipio_nome'] . ' • ' . $quiz['status_curadoria']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </article>

    <article class="panel-card">
        <p class="eyebrow">Educacao recente</p>
        <h2>Atividades em movimento</h2>
        <div class="admin-link-list">
            <?php foreach (($recentActivities ?? []) as $activity): ?>
                <a href="<?= e(url('admin/educacao/' . $activity['id_atividade'] . '/editar')); ?>">
                    <strong><?= e($activity['titulo']); ?></strong>
                    <span><?= e($activity['municipio_nome'] . ' • ' . $activity['status_curadoria']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </article>
</section>
