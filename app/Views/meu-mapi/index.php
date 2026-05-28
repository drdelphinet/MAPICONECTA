<section class="container page-header">
    <p class="eyebrow">Area do usuario</p>
    <h1>Meu MAPI</h1>
    <p>Seu painel pessoal para acompanhar progresso, medalhas, quizzes respondidos e ritmo de exploracao dentro da plataforma.</p>
</section>

<section class="container my-mapi-hero">
    <article class="panel-card my-mapi-hero-main">
        <p class="eyebrow">Visao geral</p>
        <h2><?= e($user['nome'] ?? 'Explorador'); ?>, sua jornada no MAPI comeca a ganhar memoria.</h2>
        <p>Este espaco organiza pontuacao, historico e conquistas para que a experiencia continue de onde voce parou.</p>
    </article>

    <article class="panel-card my-mapi-hero-side">
        <p class="eyebrow">Proximo foco</p>
        <h2>Continue explorando municipios e respondendo quizzes.</h2>
        <p>Quanto mais cidades voce visitar, mais sentido essa area pessoal vai ganhar para recomendacoes e acompanhamento.</p>
    </article>
</section>

<section class="container stats-grid">
    <article class="stat-card">
        <span>Pontuacao total</span>
        <strong><?= (int) $stats['pontuacao_total']; ?></strong>
    </article>
    <article class="stat-card">
        <span>Medalhas</span>
        <strong><?= (int) $stats['medalhas']; ?></strong>
    </article>
    <article class="stat-card">
        <span>Municipios visitados</span>
        <strong><?= (int) $stats['municipios_visitados']; ?></strong>
    </article>
    <article class="stat-card">
        <span>Municipios favoritos</span>
        <strong><?= (int) $stats['municipios_favoritos']; ?></strong>
    </article>
    <article class="stat-card">
        <span>Quizzes respondidos</span>
        <strong><?= (int) $stats['quizzes_respondidos']; ?></strong>
    </article>
    <article class="stat-card">
        <span>Exploracao do estado</span>
        <strong><?= (int) $stats['exploracao_percentual']; ?>%</strong>
    </article>
</section>

<section class="container my-mapi-grid">
    <article class="panel-card">
        <p class="eyebrow">Perfil</p>
        <h2>Identidade da conta</h2>
        <p><strong>Nome:</strong> <?= e($user['nome'] ?? '-'); ?></p>
        <p><strong>Email:</strong> <?= e($user['email'] ?? '-'); ?></p>
        <p><strong>Perfil:</strong> <?= e($user['role_name'] ?? '-'); ?></p>
    </article>

    <article class="panel-card">
        <p class="eyebrow">Conquistas</p>
        <h2>Medalhas conquistadas</h2>
        <?php if (($medals ?? []) === []): ?>
            <p>Nenhuma medalha ainda. Responda quizzes para desbloquear conquistas e dar mais corpo ao seu historico.</p>
        <?php else: ?>
            <div class="medal-grid">
                <?php foreach ($medals as $medal): ?>
                    <article class="panel-card medal-card">
                        <h3><?= e($medal['nome']); ?></h3>
                        <p><?= e($medal['descricao'] ?? ''); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </article>
</section>

<section class="container my-mapi-grid">
    <article class="panel-card">
        <p class="eyebrow">Resumo rapido</p>
        <h2>O que esta ativo hoje</h2>
        <ul class="feature-list">
            <li>Pontuacao e quizzes respondidos ja alimentam sua evolucao.</li>
            <li>Medalhas registram conquistas concluidas.</li>
            <li>Favoritos e visitados agora podem ser marcados diretamente nas paginas dos municipios.</li>
        </ul>
    </article>

    <article class="panel-card warm-panel">
        <p class="eyebrow">Sugestao</p>
        <h2>Volte ao mapa para escolher o proximo municipio.</h2>
        <p>O caminho mais natural daqui e continuar a exploracao territorial e abrir novos quizzes municipais.</p>
        <div class="hero-actions">
            <a class="button" href="<?= e(url('mapa')); ?>">Abrir mapa</a>
            <a class="button button-outline" href="<?= e(url('municipios')); ?>">Ver municipios</a>
        </div>
    </article>
</section>

<section class="container section-heading">
    <p class="eyebrow">Colecoes pessoais</p>
    <h2>Municipios que voce salvou ao longo da exploracao.</h2>
</section>

<section class="container my-mapi-grid">
    <article class="panel-card">
        <p class="eyebrow">Favoritos</p>
        <h2>Municipios salvos</h2>
        <?php if (($favoriteMunicipalities ?? []) === []): ?>
            <p>Voce ainda nao marcou nenhum municipio como favorito.</p>
        <?php else: ?>
            <div class="cards-grid compact-cards-grid">
                <?php foreach ($favoriteMunicipalities as $municipality): ?>
                    <article class="panel-card municipality-card">
                        <p class="eyebrow"><?= e($municipality['estado_sigla']); ?></p>
                        <h3><?= e($municipality['nome']); ?></h3>
                        <a class="button button-outline" href="<?= e(url('municipio/' . $municipality['slug'])); ?>">Abrir municipio</a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </article>

    <article class="panel-card">
        <p class="eyebrow">Visitados</p>
        <h2>Municipios marcados como visitados</h2>
        <?php if (($visitedMunicipalities ?? []) === []): ?>
            <p>Voce ainda nao marcou nenhum municipio como visitado.</p>
        <?php else: ?>
            <div class="cards-grid compact-cards-grid">
                <?php foreach ($visitedMunicipalities as $municipality): ?>
                    <article class="panel-card municipality-card">
                        <p class="eyebrow"><?= e($municipality['estado_sigla']); ?></p>
                        <h3><?= e($municipality['nome']); ?></h3>
                        <p><?= e((string) $municipality['visitado_em']); ?></p>
                        <a class="button button-outline" href="<?= e(url('municipio/' . $municipality['slug'])); ?>">Abrir municipio</a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </article>
</section>

<section class="container section-heading">
    <p class="eyebrow">Proximos passos</p>
    <h2>Sugestoes para continuar ampliando sua exploracao.</h2>
</section>

<section class="container cards-grid">
    <?php if (($recommendedMunicipalities ?? []) === []): ?>
        <article class="panel-card municipalities-empty-card">
            <p class="eyebrow">Tudo explorado por enquanto</p>
            <h2>Voce ja marcou todos os municipios publicados como visitados.</h2>
            <p>O proximo ganho agora e ampliar o acervo publicado no painel ou voltar aos quizzes para fortalecer a pontuacao.</p>
        </article>
    <?php else: ?>
        <?php foreach ($recommendedMunicipalities as $municipality): ?>
            <article class="panel-card municipality-card municipality-card-rich">
                <p class="eyebrow"><?= e($municipality['estado_sigla']); ?></p>
                <h3><?= e($municipality['nome']); ?></h3>
                <p><?= e($municipality['descricao_curta'] ?: 'Conteudo introdutorio em construcao para este municipio.'); ?></p>
                <a class="button button-outline" href="<?= e(url('municipio/' . $municipality['slug'])); ?>">Explorar agora</a>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

<section class="container section-heading">
    <p class="eyebrow">Historico</p>
    <h2>Quizzes respondidos</h2>
</section>

<section class="container table-card">
    <?php if (($history ?? []) === []): ?>
        <div class="table-empty-state">
            <h3>Nenhum quiz respondido ainda</h3>
            <p>Assim que voce concluir quizzes, este historico passa a mostrar sua evolucao por municipio.</p>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Quiz</th>
                    <th>Municipio</th>
                    <th>Pontos</th>
                    <th>Acertos</th>
                    <th>Concluido em</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($history ?? []) as $entry): ?>
                    <tr>
                        <td><?= e($entry['quiz_titulo']); ?></td>
                        <td><?= e($entry['municipio_nome']); ?></td>
                        <td><?= (int) $entry['pontuacao_total']; ?></td>
                        <td><?= (int) $entry['total_acertos']; ?>/<?= (int) $entry['total_perguntas']; ?></td>
                        <td><?= e((string) ($entry['concluido_em'] ?? $entry['atualizado_em'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<section class="container section-heading">
    <p class="eyebrow">Ranking</p>
    <h2>Top exploradores por pontos</h2>
</section>

<section class="container table-card">
    <?php if (($ranking ?? []) === []): ?>
        <div class="table-empty-state">
            <h3>Ranking ainda vazio</h3>
            <p>Quando houver progresso registrado, este quadro vai destacar os usuarios com maior pontuacao.</p>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Usuario</th>
                    <th>Pontos</th>
                    <th>Quizzes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($ranking ?? []) as $index => $entry): ?>
                    <tr>
                        <td><?= $index + 1; ?></td>
                        <td><?= e($entry['nome']); ?></td>
                        <td><?= (int) $entry['pontuacao_total']; ?></td>
                        <td><?= (int) $entry['quizzes_respondidos']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
