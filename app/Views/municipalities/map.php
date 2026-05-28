<section class="container page-header">
    <p class="eyebrow">Exploracao geografica</p>
    <h1>Mapa interativo dos municipios</h1>
    <p>Comece pelo territorio: filtre por estado ou municipio, abra uma cidade e siga para conteudo, quiz e exploracao cultural.</p>
</section>

<section class="container toolbar-card">
    <form method="get" action="<?= e(url('mapa')); ?>" class="filter-grid">
        <label class="field">
            <span>Estado</span>
            <select name="estado">
                <option value="">Todos</option>
                <?php foreach ($states as $state): ?>
                    <option value="<?= e($state['slug']); ?>" <?= ($selectedStateSlug === $state['slug']) ? 'selected' : ''; ?>>
                        <?= e($state['nome'] . ' (' . $state['sigla'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="field">
            <span>Municipio</span>
            <select name="municipio">
                <option value="">Todos</option>
                <?php foreach ($municipalities as $municipality): ?>
                    <option value="<?= e($municipality['slug']); ?>" <?= ($selectedMunicipalitySlug === $municipality['slug']) ? 'selected' : ''; ?>>
                        <?= e($municipality['nome']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <div class="toolbar-actions align-end">
            <button type="submit" class="button">Aplicar filtros</button>
            <a class="button button-outline" href="<?= e(url('mapa')); ?>">Limpar</a>
        </div>
    </form>
</section>

<section class="container map-layout">
    <div class="map-panel">
        <div
            id="municipios-map"
            class="leaflet-map"
            data-markers='<?= e(json_encode($markers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?>'
            data-map-items='<?= e(json_encode($geoMunicipalities, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?>'
            data-map-lookup='<?= e(json_encode($geoMunicipalityLookup, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?>'
            data-geojson-url="<?= e(asset('assets/data/piaui-municipios.geojson')); ?>"
            data-selected-municipality="<?= e($selectedMunicipalitySlug); ?>"
        ></div>

        <?php if ($markers === [] && ($selectedMunicipality ?? null) !== null && empty($selectedMunicipalityHasCoordinates)): ?>
            <div class="map-empty">
                <strong><?= e($selectedMunicipality['nome']); ?></strong> ja esta publicado, mas ainda nao tem latitude e longitude cadastradas.
                <?php if (!empty($selectedMunicipalityHasGeoShape)): ?>
                    A malha territorial atual ja contem esse municipio, entao o contorno deve aparecer no mapa mesmo sem marcador.
                <?php else: ?>
                    A malha territorial atual ainda nao contem esse municipio, entao por enquanto so a pagina publica dele fica disponivel.
                <?php endif; ?>
                <div class="map-empty-actions">
                    <a class="button button-outline" href="<?= e(url('municipio/' . $selectedMunicipality['slug'])); ?>">Abrir pagina do municipio</a>
                </div>
            </div>
        <?php elseif ($markers === []): ?>
            <div class="map-empty">
                Nenhum municipio publicado com latitude e longitude foi encontrado para os filtros atuais.
            </div>
        <?php endif; ?>

        <div class="map-legend">
            <div class="map-legend-item">
                <span class="map-swatch map-swatch-territory"></span>
                <span>Municipio publicado na malha territorial</span>
            </div>
            <div class="map-legend-item">
                <span class="map-swatch map-swatch-selected"></span>
                <span>Municipio selecionado no filtro</span>
            </div>
            <div class="map-legend-item">
                <span class="map-swatch map-swatch-marker"></span>
                <span>Marcador com latitude e longitude</span>
            </div>
        </div>
    </div>

    <aside class="map-sidebar">
        <?php if (($selectedMunicipality ?? null) !== null): ?>
            <article class="panel-card map-selection-card">
                <p class="eyebrow">Municipio selecionado</p>
                <h2><?= e($selectedMunicipality['nome']); ?></h2>
                <p><?= e($selectedMunicipality['descricao_curta'] ?: 'Municipio publicado no acervo do MAPI CONECTA.'); ?></p>
                <div class="workflow-pill-row">
                    <span class="story-pill"><?= !empty($selectedMunicipalityHasCoordinates) ? 'Com marcador' : 'Sem marcador'; ?></span>
                    <span class="story-pill"><?= !empty($selectedMunicipalityHasGeoShape) ? 'Com contorno territorial' : 'Sem contorno nesta malha'; ?></span>
                    <span class="story-pill">Codigo IBGE <?= e((string) $selectedMunicipality['codigo_ibge']); ?></span>
                </div>
                <div class="hero-actions">
                    <a class="button button-outline" href="<?= e(url('municipio/' . $selectedMunicipality['slug'])); ?>">Abrir pagina</a>
                </div>
            </article>
        <?php endif; ?>

        <article class="panel-card">
            <h2>Como usar</h2>
            <ol class="journey-list">
                <li>Escolha um recorte no filtro.</li>
                <li>Abra um marcador no mapa.</li>
                <li>Entre na pagina do municipio.</li>
                <li>Continue para conteudo e quiz.</li>
            </ol>
        </article>

        <article class="panel-card">
            <h2>Preparado para evoluir</h2>
            <ul class="feature-list">
                <li>Suporte a GeoJSON territorial do IBGE Malhas</li>
                <li>Agrupamento por regiao ou categoria</li>
                <li>Destaque de pontos turisticos e QR Codes</li>
                <li>Camadas tematicas de indicadores municipais</li>
            </ul>
        </article>

        <article class="panel-card">
            <h2>Total oficial do estado</h2>
            <strong class="map-counter"><?= (int) ($officialMunicipalities ?? 0); ?></strong>
            <p>municipio(s) no recorte territorial esperado para este estado.</p>
        </article>

        <article class="panel-card">
            <h2>Acervo publicado</h2>
            <strong class="map-counter"><?= (int) ($publishedMunicipalities ?? count($geoMunicipalities ?? [])); ?></strong>
            <p>municipio(s) ja liberado(s) para navegacao publica neste recorte.</p>
        </article>

        <article class="panel-card">
            <h2>Municipios no mapa</h2>
            <strong class="map-counter"><?= count($markers); ?></strong>
            <p>marcadores ativos para este recorte.</p>
        </article>

        <article class="panel-card">
            <h2>Camada territorial</h2>
            <strong class="map-counter"><?= count($geoMunicipalities ?? []); ?></strong>
            <p>municipio(s) publicado(s) disponivel(is) para vinculo com a malha GeoJSON neste recorte.</p>
        </article>

        <article class="panel-card">
            <h2>Cobertura atual</h2>
            <strong class="map-counter">
                <?= (int) ($markerCoveragePercent ?? 0); ?>%
            </strong>
            <p>do acervo publicado deste recorte ja aparece como marcador com latitude e longitude preenchidas.</p>
        </article>

        <article class="panel-card">
            <h2>Cobertura do estado</h2>
            <strong class="map-counter"><?= (int) ($officialCoveragePercent ?? 0); ?>%</strong>
            <p>do total oficial do estado ja esta visivel com marcador neste recorte.</p>
        </article>

        <?php if (($publishedWithoutCoordinatesCount ?? 0) > 0): ?>
            <article class="panel-card warm-panel">
                <h2>Fora do mapa por enquanto</h2>
                <strong class="map-counter"><?= (int) $publishedWithoutCoordinatesCount; ?></strong>
                <p>municipio(s) publicado(s) ainda sem latitude e longitude. Eles ja podem existir na area publica, mas nao aparecem como marcador enquanto nao tiverem coordenadas ou malha territorial vinculada.</p>
            </article>
        <?php endif; ?>

        <?php if (($publishedMissingFromGeoJsonCount ?? 0) > 0): ?>
            <article class="panel-card warning-panel">
                <h2>Fora da malha territorial</h2>
                <strong class="map-counter"><?= (int) $publishedMissingFromGeoJsonCount; ?></strong>
                <p>municipio(s) publicado(s) ainda nao existem no arquivo territorial atual. Eles podem ter pagina e ate marcador, mas o contorno nao aparece enquanto a malha nao for atualizada.</p>
            </article>
        <?php endif; ?>

        <?php if ($markers !== []): ?>
            <article class="panel-card">
                <h2>Atalhos do recorte</h2>
                <div class="map-results-list">
                    <?php foreach (array_slice($markers, 0, 6) as $municipality): ?>
                        <a class="map-results-item" href="<?= e(url('municipio/' . $municipality['slug'])); ?>">
                            <strong><?= e($municipality['nome']); ?></strong>
                            <span><?= e($municipality['estado_sigla']); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </article>
        <?php endif; ?>

        <?php if (($publishedWithoutCoordinates ?? []) !== []): ?>
            <article class="panel-card">
                <h2>Publicados aguardando coordenadas</h2>
                <div class="map-results-list">
                    <?php foreach (($publishedWithoutCoordinates ?? []) as $municipality): ?>
                        <a class="map-results-item" href="<?= e(url('municipio/' . $municipality['slug'])); ?>">
                            <strong><?= e($municipality['nome']); ?></strong>
                            <span><?= e($municipality['estado_sigla']); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </article>
        <?php endif; ?>

        <?php if (($publishedMissingFromGeoJson ?? []) !== []): ?>
            <article class="panel-card">
                <h2>Publicados fora da malha</h2>
                <div class="map-results-list">
                    <?php foreach (($publishedMissingFromGeoJson ?? []) as $municipality): ?>
                        <a class="map-results-item" href="<?= e(url('municipio/' . $municipality['slug'])); ?>">
                            <strong><?= e($municipality['nome']); ?></strong>
                            <span><?= e($municipality['estado_sigla']); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </article>
        <?php endif; ?>

        <article class="panel-card">
            <h2>Por que isso importa</h2>
            <p>O mapa funciona como porta de entrada para aprendizagem regional, turismo local e descoberta autonoma do Piaui.</p>
        </article>

        <article class="panel-card">
            <h2>Mapa territorial</h2>
            <p>Assim que o arquivo <code>public/assets/data/piaui-municipios.geojson</code> estiver presente, o sistema passa a desenhar os contornos reais dos municipios como areas clicaveis.</p>
        </article>
    </aside>
</section>
