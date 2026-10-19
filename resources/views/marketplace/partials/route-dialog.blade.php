<dialog id="store-route-dialog" class="store-info-dialog store-route-dialog" aria-labelledby="store-route-title" data-store-dialog>
    <div class="store-dialog-heading"><div><span class="buyer-eyebrow">Desde tu ubicación guardada</span><h2 id="store-route-title" data-route-title>Cómo llegar a la tienda</h2></div><button type="button" data-dialog-close aria-label="Cerrar ruta"><x-market-icon name="close"/></button></div>
    <div class="store-dialog-body">
        <p class="route-status" data-route-status role="status" aria-live="polite">Calculando la ruta…</p>
        <div class="route-map" data-route-map role="region" aria-label="Mapa de la ruta a la tienda"></div>
        <div class="route-summary" data-route-summary hidden></div>
        <p class="buyer-muted">Ruta en automóvil desde el punto que guardaste. Tiempo estimado sin tráfico en tiempo real.</p>
        <a class="buyer-button" data-route-google target="_blank" rel="noopener noreferrer">Abrir ruta en Google Maps ↗</a>
        <details class="route-steps" data-route-steps-panel hidden><summary>Indicaciones para llegar</summary><ol data-route-steps></ol></details>
        <p class="route-attribution">Mapa: OpenStreetMap · Cálculo de ruta: OSRM</p>
    </div>
</dialog>
