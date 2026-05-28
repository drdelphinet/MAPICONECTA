(function () {
    const mapElement = document.getElementById('municipality-local-map');

    if (!mapElement || typeof L === 'undefined') {
        return;
    }

    const points = JSON.parse(mapElement.dataset.mapPoints || '[]');

    if (!Array.isArray(points) || points.length === 0) {
        return;
    }

    const municipalityPoint = points.find(function (item) {
        return item.type === 'municipality';
    }) || points[0];

    const defaultCenter = [
        Number(municipalityPoint.latitude) || -8.28,
        Number(municipalityPoint.longitude) || -42.7
    ];

    const map = L.map(mapElement, {
        scrollWheelZoom: false,
    }).setView(defaultCenter, points.length > 1 ? 11 : 12);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    const bounds = [];

    points.forEach(function (item) {
        const lat = Number(item.latitude);
        const lng = Number(item.longitude);

        if (Number.isNaN(lat) || Number.isNaN(lng)) {
            return;
        }

        const markerOptions = item.type === 'municipality'
            ? {}
            : {
                radius: 8,
                color: '#b45309',
                weight: 2,
                fillColor: '#f59e0b',
                fillOpacity: 0.95,
            };

        const marker = item.type === 'municipality'
            ? L.marker([lat, lng], markerOptions)
            : L.circleMarker([lat, lng], markerOptions);

        marker.addTo(map).bindPopup(buildPopup(item));
        bounds.push([lat, lng]);
    });

    if (bounds.length > 1) {
        map.fitBounds(bounds, { padding: [28, 28] });
    }

    function buildPopup(item) {
        const description = item.description && String(item.description).trim() !== ''
            ? String(item.description)
            : 'Ponto publicado no acervo territorial do municipio.';
        const linkUrl = item.link_url ? String(item.link_url) : '';
        const linkLabel = item.link_label ? String(item.link_label) : 'Abrir';

        return '<div class="map-popup">' +
            '<strong>' + escapeHtml(item.name || 'Ponto no mapa') + '</strong><br>' +
            '<span>' + escapeHtml(item.type === 'municipality' ? 'Municipio base' : 'Ponto turistico') + '</span><br><br>' +
            '<span>' + escapeHtml(description) + '</span>' +
            (linkUrl !== '' ? '<br><br><a href="' + escapeAttribute(linkUrl) + '">' + escapeHtml(linkLabel) + '</a>' : '') +
            '</div>';
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
