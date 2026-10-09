// The same INEGI municipal boundary is used by the server and both maps.
function inRing(x, y, ring) {
    let inside = false;
    for (let i = 0, j = ring.length - 1; i < ring.length; j = i++) {
        const [xi, yi] = ring[i], [xj, yj] = ring[j];
        const cross = (x - xi) * (yj - yi) - (y - yi) * (xj - xi);
        if (Math.abs(cross) < 1e-12 && x >= Math.min(xi, xj) && x <= Math.max(xi, xj) && y >= Math.min(yi, yj) && y <= Math.max(yi, yj)) return true;
        if ((yi > y) !== (yj > y) && x < (xj - xi) * (y - yi) / (yj - yi) + xi) inside = !inside;
    }
    return inside;
}

function withinManzanillo(point, boundary) {
    return boundary.features[0].geometry.coordinates.some(polygon => inRing(point.lng, point.lat, polygon[0]) && !polygon.slice(1).some(hole => inRing(point.lng, point.lat, hole)));
}

export async function initializeLocationMap(form, { container, status, locate, restricted = false }) {
    const lat = form.querySelector('[name="latitud"]');
    const lng = form.querySelector('[name="longitud"]');
    // Keep coordinates usable even if tiles cannot load.
    const valid = () => lat.value !== '' && lng.value !== '' && Number.isFinite(Number(lat.value)) && Number.isFinite(Number(lng.value)) && Math.abs(Number(lat.value)) <= 90 && Math.abs(Number(lng.value)) <= 180;
    let selectPoint = point => {
        lat.value = Number(point.lat).toFixed(7);
        lng.value = Number(point.lng).toFixed(7);
        status.textContent = 'Ubicación obtenida. Completa los detalles de tu dirección.';
    };
    locate.addEventListener('click', () => {
        if (!navigator.geolocation) { status.textContent = 'Este navegador no permite consultar tu ubicación. Selecciona el punto en el mapa.'; return; }
        locate.disabled = true;
        status.textContent = 'Buscando tu ubicación…';
        navigator.geolocation.getCurrentPosition(({ coords }) => {
            selectPoint({ lat: coords.latitude, lng: coords.longitude }, true, true);
            locate.disabled = false;
        }, (error) => {
            locate.disabled = false;
            status.textContent = error.code === 1 ? 'Permiso de ubicación denegado. Puedes seleccionar tu ubicación en el mapa o completar la dirección manualmente.' : 'No pudimos obtener tu ubicación. Selecciona el punto en el mapa o completa la dirección manualmente.';
        }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 });
    });
    try {
        const [{ default: L }, , boundary] = await Promise.all([
            import('leaflet'), import('leaflet/dist/leaflet.css'),
            fetch('/geo/manzanillo.json').then(response => { if (!response.ok) throw new Error('Boundary unavailable'); return response.json(); }).catch(() => null),
        ]);
        const map = L.map(container, { scrollWheelZoom: false }).setView(valid() ? [Number(lat.value), Number(lng.value)] : [19.0522, -104.3158], valid() ? 16 : 13);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>' }).addTo(map);
        if (restricted && boundary) L.geoJSON(boundary, { interactive: false, style: { color: '#C34722', weight: 2, fillOpacity: 0.06 }, attribution: 'Límite municipal: INEGI, Marco Geoestadístico 2025' }).addTo(map);
        const icon = L.divIcon({ className: 'seller-map-marker', iconSize: [30, 30], iconAnchor: [15, 30] });
        let marker;
        selectPoint = (point, center = false, updateAddress = false) => {
            lat.value = Number(point.lat).toFixed(7);
            lng.value = Number(point.lng).toFixed(7);
            if (!marker) {
                marker = L.marker(point, { icon, draggable: true, title: restricted ? 'Ubicación del negocio' : 'Tu ubicación' }).addTo(map);
                marker.on('dragend', () => selectPoint(marker.getLatLng(), false, true));
            } else marker.setLatLng(point);
            if (center) map.setView(point, 16);
            const inside = boundary ? withinManzanillo(point, boundary) : null;
            if (inside && updateAddress) {
                form.querySelector('[name="ciudad"]').value = 'Manzanillo';
                form.querySelector('[name="estado"]').value = 'Colima';
            }
            status.dataset.outside = String(restricted && inside === false);
            status.textContent = restricted && inside === false ? 'Esta ubicación está fuera de Manzanillo. NexoEco apoya a los microemprendimientos de este municipio; elige un punto dentro de la zona marcada para crear tu tienda.' : restricted ? 'Ubicación seleccionada. El servidor verificará que tu negocio esté dentro de Manzanillo.' : inside ? 'Ubicación en Manzanillo seleccionada. Completa colonia, calle, número y código postal; puedes ajustar el marcador.' : 'Ubicación seleccionada. Completa ciudad, estado y los detalles de tu domicilio; puedes ajustar el marcador.';
            container.dataset.selected = 'true';
        };
        if (valid()) selectPoint({ lat: Number(lat.value), lng: Number(lng.value) }, false, form.dataset.locationPrefill === 'true');
        delete form.dataset.locationPrefill;
        map.on('click', event => selectPoint(event.latlng, false, true));
        [lat, lng].forEach(input => input.addEventListener('change', () => { if (valid()) selectPoint({ lat: Number(lat.value), lng: Number(lng.value) }, true, true); }));
        new ResizeObserver(() => map.invalidateSize()).observe(container);
        container.dataset.ready = 'true';
    } catch {
        status.textContent = 'El mapa no está disponible. Puedes usar tu ubicación actual o completar la dirección manualmente.';
        container.dataset.unavailable = 'true';
    }
}
