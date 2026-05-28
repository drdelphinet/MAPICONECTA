<section class="container page-header">
    <p class="eyebrow">Gamificacao</p>
    <h1>Ranking</h1>
    <p>Veja quem acumulou mais pontos nos quizzes publicados e como a exploracao vem se transformando em progresso.</p>
</section>

<section class="container result-summary-grid">
    <article class="panel-card">
        <p class="eyebrow">Como ler</p>
        <h2>O ranking resume consistencia, nao apenas um acerto isolado.</h2>
        <p>Quem aparece no topo tende a combinar recorrencia, curiosidade e participacao ativa em diferentes quizzes municipais.</p>
    </article>

    <article class="panel-card warm-panel">
        <p class="eyebrow">Convite</p>
        <h2>Entre no mapa, escolha uma cidade e avance no proximo quiz.</h2>
        <p>Essa pagina existe para alimentar motivacao e manter a camada de gamificacao viva.</p>
    </article>
</section>

<section class="container table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Usuario</th>
                <th>Pontos</th>
                <th>Quizzes respondidos</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ranking as $index => $entry): ?>
                <tr>
                    <td><?= $index + 1; ?></td>
                    <td><?= e($entry['nome']); ?></td>
                    <td><?= (int) $entry['pontuacao_total']; ?></td>
                    <td><?= (int) $entry['quizzes_respondidos']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
