import { distanceKm, locationHasDrifted, routeDistance } from './discovery-geo';

export function initializeLocalDiscovery() {
    const awareness = document.querySelector('[data-location-awareness]');
    if (awareness && navigator.geolocation) {
        const check = awareness.querySelector('[data-location-check]');
        const update = awareness.querySelector('[data-location-update]');
        const keep = awareness.querySelector('[data-location-keep]');
        const status = awareness.querySelector('[data-location-check-status]');
        const saved = [Number(awareness.dataset.lat), Number(awareness.dataset.lng)];
        let detected;
        let checking = false;
        const compareLocation = () => {
            if (checking) return;
            checking = true;
            check.disabled = true;
            status.textContent = 'Comprobando tu ubicación actual…';
            navigator.geolocation.getCurrentPosition(({ coords }) => {
                detected = [coords.latitude, coords.longitude];
                const far = locationHasDrifted(saved, detected, coords.accuracy, Number(awareness.dataset.driftKm));
                awareness.classList.toggle('location-drifted', far);
                update.hidden = !far;
                keep.hidden = !far;
                status.textContent = far
                    ? `Estás a unos ${routeDistance(distanceKm(saved, detected) * 1000)} de tu ubicación guardada. ¿Quieres buscar tiendas desde donde estás ahora?`
                    : coords.accuracy > 2000 ? 'La ubicación actual es poco precisa. Revisa tu punto en el mapa para confirmar dónde estás.'
                        : 'Tu ubicación actual está cerca del punto guardado. Seguimos mostrando las tiendas de esa zona.';
                checking = false;
                check.disabled = false;
            }, (error) => {
                checking = false;
                check.disabled = false;
                status.textContent = error.code === 1 ? 'No se concedió acceso a tu ubicación. Puedes cambiar el punto guardado desde el mapa.' : 'No pudimos comprobar tu ubicación actual. Puedes revisar el punto guardado desde el mapa.';
            }, { enableHighAccuracy: false, timeout: 10000, maximumAge: 60000 });
        };
        check.addEventListener('click', compareLocation);
        keep.addEventListener('click', () => {
            awareness.classList.remove('location-drifted');
            update.hidden = keep.hidden = true;
            status.textContent = 'Seguimos usando tu ubicación guardada. Puedes cambiarla cuando quieras.';
        });
        update.addEventListener('click', () => {
            if (!detected) return;
            const form = document.querySelector('[data-buyer-location-form]');
            if (!form) return;
            form.querySelector('[name="latitud"]').value = detected[0].toFixed(7);
            form.querySelector('[name="longitud"]').value = detected[1].toFixed(7);
            ['ciudad', 'estado', 'direccion', 'colonia', 'codigo_postal'].forEach(name => { form.querySelector(`[name="${name}"]`).value = ''; });
            form.dataset.locationPrefill = 'true';
            form.querySelector('[name="latitud"]').dispatchEvent(new Event('change'));
            form.querySelector('[data-location-map-status]').textContent = 'Ubicación actual seleccionada. Completa la dirección y guarda para actualizar tus tiendas cercanas.';
        });
        // Only compare automatically when permission was already granted; otherwise use the button.
        navigator.permissions?.query({ name: 'geolocation' }).then(permission => {
            if (permission.state === 'granted') compareLocation();
        }).catch(() => {});
    }

    const dialog = document.querySelector('#store-route-dialog');
    if (!dialog) return;
    const status = dialog.querySelector('[data-route-status]');
    const summary = dialog.querySelector('[data-route-summary]');
    const steps = dialog.querySelector('[data-route-steps]');
    const stepsPanel = dialog.querySelector('[data-route-steps-panel]');
    const container = dialog.querySelector('[data-route-map]');
    let controller;
    let generation = 0;
    let map;
    let layers;
    const markerLabel = text => { const node = document.createElement('span'); node.textContent = text; return node; };
    document.querySelectorAll('[data-store-route]').forEach(button => button.addEventListener('click', async () => {
        controller?.abort();
        controller = new AbortController();
        const current = ++generation;
        dialog.querySelector('[data-route-title]').textContent = `Cómo llegar a ${button.dataset.storeName}`;
        dialog.querySelector('[data-route-google]').href = button.dataset.mapsUrl;
        status.textContent = 'Calculando tu ruta por calles…';
        summary.hidden = stepsPanel.hidden = true;
        stepsPanel.open = false;
        steps.replaceChildren();
        layers?.clearLayers();
        container.hidden = true;
        try {
            const response = await fetch(button.dataset.routeUrl, { signal: controller.signal, credentials: 'same-origin', cache: 'no-store', headers: { Accept: 'application/json' } });
            const data = response.headers.get('content-type')?.includes('application/json') ? await response.json() : null;
            if (current !== generation) return;
            if (!response.ok || !data?.geometry) throw new Error([401, 419].includes(response.status) ? 'Inicia sesión de nuevo para consultar la ruta.' : response.status === 403 ? 'Verifica tu correo para consultar la ruta.' : data?.message || 'No pudimos calcular la ruta. Puedes abrirla en Google Maps.');
            summary.textContent = `${routeDistance(data.distance)} por calles · ${Math.max(1, Math.round(data.duration / 60))} min estimados · En automóvil`;
            summary.hidden = false;
            data.steps.forEach(step => {
                const item = document.createElement('li');
                item.textContent = `${step.instruction}${step.distance > 0 ? ' · ' + routeDistance(step.distance) : ''}`;
                steps.append(item);
            });
            stepsPanel.hidden = data.steps.length === 0;
            status.textContent = 'Ruta calculada. Puedes ver las indicaciones o continuar en Google Maps.';
            try {
                const [{ default: L }] = await Promise.all([import('leaflet'), import('leaflet/dist/leaflet.css')]);
                if (current !== generation || !dialog.open) return;
                container.hidden = false;
                if (!map) {
                    map = L.map(container, { scrollWheelZoom: false });
                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>' }).addTo(map);
                    layers = L.layerGroup().addTo(map);
                    new ResizeObserver(() => map.invalidateSize()).observe(container);
                }
                const line = L.geoJSON(data.geometry, { style: { color: '#C34722', weight: 5, opacity: .9 } }).addTo(layers);
                L.circleMarker(data.origin, { radius: 8, color: '#FFF', weight: 3, fillColor: '#2F7EE8', fillOpacity: 1 }).bindPopup(markerLabel('Tu ubicación guardada')).addTo(layers);
                L.circleMarker(data.destination, { radius: 9, color: '#FFF', weight: 3, fillColor: '#C34722', fillOpacity: 1 }).bindPopup(markerLabel(button.dataset.storeName)).addTo(layers);
                map.invalidateSize();
                map.fitBounds(line.getBounds().extend(data.origin).extend(data.destination), { padding: [28, 28], maxZoom: 17 });
            } catch {
                if (current === generation) status.textContent = 'Las indicaciones están disponibles, pero el mapa no pudo cargar. También puedes abrir la ruta en Google Maps.';
            }
        } catch (error) {
            if (error.name !== 'AbortError' && current === generation) status.textContent = error.message;
        }
    }));
    dialog.addEventListener('close', () => { controller?.abort(); generation++; });
}
