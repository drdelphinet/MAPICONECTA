(function () {
    const mapElement = document.getElementById('municipios-map');

    if (!mapElement || typeof L === 'undefined') {
        return;
    }

    const markers = JSON.parse(mapElement.dataset.markers || '[]');
    const mapItems = JSON.parse(mapElement.dataset.mapItems || '[]');
    const mapLookup = JSON.parse(mapElement.dataset.mapLookup || '{}');
    const geoJsonUrl = mapElement.dataset.geojsonUrl || '';
    const selectedMunicipalitySlug = mapElement.dataset.selectedMunicipality || '';
    const defaultCenter = [-8.28, -42.7];
    const defaultZoom = 7;

    const map = L.map(mapElement, {
        scrollWheelZoom: true,
    }).setView(defaultCenter, defaultZoom);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    const bounds = [];
    const municipalitiesBySlug = {};
    const municipalitiesByName = {};
    const municipalitiesByCode = {};

    mapItems.forEach(function (item) {
        municipalitiesBySlug[item.slug] = item;
        municipalitiesByName[slugify(item.nome)] = item;
        if (item.codigo_ibge) {
            municipalitiesByCode[String(item.codigo_ibge)] = item;
        }
    });

    renderMarkers();
    loadGeoJsonLayer();

    function renderMarkers() {
        markers.forEach(function (item) {
            const lat = Number(item.latitude);
            const lng = Number(item.longitude);

            if (Number.isNaN(lat) || Number.isNaN(lng)) {
                return;
            }

            const marker = L.marker([lat, lng]).addTo(map);
            marker.bindPopup(buildPopup(item));
            bounds.push([lat, lng]);
        });

        if (bounds.length === 1) {
            map.setView(bounds[0], 11);
        } else if (bounds.length > 1) {
            map.fitBounds(bounds, { padding: [32, 32] });
        }
    }

    function loadGeoJsonLayer() {
        if (!geoJsonUrl) {
            return;
        }

        fetch(geoJsonUrl)
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('GeoJSON indisponivel');
                }

                return response.json();
            })
            .then(function (geojson) {
                let selectedLayer = null;
                const geoLayer = L.geoJSON(geojson, {
                    filter: function (feature) {
                        if (!selectedMunicipalitySlug) {
                            return true;
                        }

                        const municipality = resolveMunicipality(feature);
                        return isFeatureSelected(feature, municipality);
                    },
                    style: function (feature) {
                        const municipality = resolveMunicipality(feature);
                        const isSelected = isFeatureSelected(feature, municipality);

                        return {
                            color: isSelected ? '#b45309' : '#0f766e',
                            weight: isSelected ? 2.4 : 1.2,
                            fillColor: isSelected ? '#d97706' : '#0f766e',
                            fillOpacity: isSelected ? 0.22 : 0.12,
                        };
                    },
                    onEachFeature: function (feature, layer) {
                        const municipality = resolveMunicipality(feature);
                        if (isFeatureSelected(feature, municipality)) {
                            selectedLayer = layer;
                        }

                        layer.on({
                            mouseover: function () {
                                layer.setStyle({
                                    weight: isFeatureSelected(feature, municipality) ? 2.8 : 2,
                                    fillOpacity: isFeatureSelected(feature, municipality) ? 0.28 : 0.22,
                                });
                            },
                            mouseout: function () {
                                geoLayer.resetStyle(layer);
                            },
                            click: function () {
                                if (municipality) {
                                    layer.bindPopup(buildPopup(municipality)).openPopup();
                                    return;
                                }

                                layer.bindPopup(buildFeaturePopup(feature)).openPopup();
                            }
                        });
                    }
                }).addTo(map);

                const geoBounds = geoLayer.getBounds();
                if (geoBounds.isValid()) {
                    map.fitBounds(geoBounds, { padding: [20, 20] });
                }

                if (selectedLayer && selectedLayer.getBounds) {
                    map.fitBounds(selectedLayer.getBounds(), { padding: [28, 28] });

                    const municipality = resolveMunicipality(selectedLayer.feature);
                    selectedLayer.bindPopup(municipality ? buildPopup(municipality) : buildFeaturePopup(selectedLayer.feature)).openPopup();
                }
            })
            .catch(function () {
                // Fallback silencioso: o mapa continua funcional com marcadores.
            });
    }

    function resolveMunicipality(feature) {
        const properties = feature && feature.properties ? feature.properties : {};
        const codeCandidates = [
            properties.id,
            properties.codigo_ibge,
            properties.code
        ].filter(Boolean);

        for (let i = 0; i < codeCandidates.length; i += 1) {
            const candidate = String(codeCandidates[i]).replace(/\D+/g, '');
            if (mapLookup[candidate]) {
                return mapLookup[candidate];
            }
            if (municipalitiesByCode[candidate]) {
                return municipalitiesByCode[candidate];
            }
        }

        const slugCandidates = [
            properties.slug,
            properties.municipio_slug,
            properties.slug_municipio
        ].filter(Boolean);

        for (let i = 0; i < slugCandidates.length; i += 1) {
            const candidate = slugify(String(slugCandidates[i]));
            if (municipalitiesBySlug[candidate]) {
                return municipalitiesBySlug[candidate];
            }
        }

        const name = featureName(feature);
        if (name) {
            const normalized = slugify(name);
            if (municipalitiesByName[normalized]) {
                return municipalitiesByName[normalized];
            }
        }

        return null;
    }

    function isFeatureSelected(feature, municipality) {
        if (!selectedMunicipalitySlug) {
            return false;
        }

        if (municipality && municipality.slug === selectedMunicipalitySlug) {
            return true;
        }

        const slug = featureSlug(feature);
        return slug !== '' && slug === selectedMunicipalitySlug;
    }

    function featureSlug(feature) {
        const properties = feature && feature.properties ? feature.properties : {};
        const slugCandidates = [
            properties.slug,
            properties.municipio_slug,
            properties.slug_municipio
        ].filter(Boolean);

        for (let i = 0; i < slugCandidates.length; i += 1) {
            const candidate = slugify(String(slugCandidates[i]));
            if (candidate) {
                return candidate;
            }
        }

        const name = featureName(feature);
        return name ? slugify(name) : '';
    }

    function featureName(feature) {
        const properties = feature && feature.properties ? feature.properties : {};

        return properties.nome || properties.name || properties.NM_MUN || properties.municipio || properties.NOME || '';
    }

    function buildPopup(item) {
        const description = item.descricao_curta && item.descricao_curta.trim() !== ''
            ? item.descricao_curta
            : 'Conteudo introdutorio em construcao para este municipio.';

        return '<div class="map-popup">' +
            '<strong>' + escapeHtml(item.nome) + '</strong><br>' +
            '<span>' + escapeHtml(item.estado_sigla) + '</span><br><br>' +
            '<span>' + escapeHtml(description) + '</span><br><br>' +
            '<a href="' + escapeAttribute(window.MAPI_BASE_URL + '/municipio/' + item.slug) + '">Abrir pagina do municipio</a>' +
            '</div>';
    }

    function buildFeaturePopup(feature) {
        const properties = feature && feature.properties ? feature.properties : {};
        const name = featureName(feature) || 'Municipio';
        const description = properties.description && String(properties.description).trim() !== ''
            ? String(properties.description)
            : 'Conteudo introdutorio em construcao para este municipio.';
        const slug = featureSlug(feature);
        const link = slug !== ''
            ? '<a href="' + escapeAttribute(window.MAPI_BASE_URL + '/municipio/' + slug) + '">Abrir pagina do municipio</a>'
            : '';

        return '<div class="map-popup">' +
            '<strong>' + escapeHtml(name) + '</strong><br>' +
            '<span>Camada territorial do MAPI CONECTA</span><br><br>' +
            '<span>' + escapeHtml(description) + '</span>' +
            (link !== '' ? '<br><br>' + link : '') +
            '</div>';
    }

    function slugify(value) {
        return String(value)
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function escapeAttribute(value) {
        return escapeHtml(value);
    }
})();
