@php($formId = $offer?->id ?? 'new')
<form method="POST" action="{{ $offer ? route('vendedor.promociones.update', $offer) : route('vendedor.promociones.store') }}" class="seller-form growth-offer-form" data-growth-compose="offer">
    @csrf @if($offer)@method('PUT')@endif
    <div class="growth-compose-layout">
        <div class="growth-compose-fields">
            <div class="seller-fields">
                <div><label for="offer-product-{{ $formId }}">¿Qué producto quieres destacar?</label><select id="offer-product-{{ $formId }}" name="id_producto" required><option value="">Selecciona un producto</option>@foreach($products as $product)<option value="{{ $product->getKey() }}" @selected($offer?->id_producto === $product->getKey())>{{ $product->nombre_producto }}</option>@endforeach</select></div>
                <div><label for="offer-title-{{ $formId }}">Dale un nombre a tu oferta</label><input id="offer-title-{{ $formId }}" name="title" value="{{ $offer?->title }}" required maxlength="100" placeholder="Un fin de semana para estrenar"></div>
                <div><label for="offer-discount-{{ $formId }}">Descuento (opcional)</label><input id="offer-discount-{{ $formId }}" name="discount_percent" type="number" min="1" max="90" value="{{ $offer?->discount_percent }}" placeholder="Por ejemplo, 15"><p class="seller-help">Porcentaje de descuento que acordarás con el comprador.</p></div>
                <div><label for="offer-enabled-{{ $formId }}">Estado de la oferta</label><select id="offer-enabled-{{ $formId }}" name="enabled"><option value="1" @selected(!$offer || $offer->enabled)>Activa</option><option value="0" @selected($offer && !$offer->enabled)>Desactivada</option></select></div>
                @if($subscription?->plan === 'pro')<div><label for="offer-start-{{ $formId }}">¿Cuándo comienza? (opcional)</label><input id="offer-start-{{ $formId }}" name="starts_at" type="datetime-local" value="{{ $offer?->starts_at->timezone('America/Mexico_City')->format('Y-m-d\TH:i') }}"><p class="seller-help">Déjalo vacío para comenzar ahora.</p></div>@endif
                <div><label for="offer-end-{{ $formId }}">¿Cuándo termina? (opcional)</label><input id="offer-end-{{ $formId }}" name="ends_at" type="datetime-local" value="{{ $offer?->ends_at?->timezone('America/Mexico_City')->format('Y-m-d\TH:i') }}"><p class="seller-help">Sin fecha de fin, estará visible mientras esté activa y tu plan siga vigente.</p></div>
                <div class="seller-field full"><label for="offer-description-{{ $formId }}">Los detalles hacen la diferencia</label><textarea id="offer-description-{{ $formId }}" name="description" maxlength="1500" rows="3" placeholder="Cuéntales qué incluye, hasta cuándo pueden aprovecharla y las condiciones.">{{ $offer?->description }}</textarea></div>
            </div>
            <p class="seller-help">Fechas en hora de Manzanillo. La oferta es informativa: el precio publicado y las condiciones del apartado se mantienen.</p>
            <button class="seller-button small" type="submit">{{ $offer ? 'Guardar cambios' : 'Crear promoción' }} <x-seller-icon name="arrow"/></button>
        </div>
        <aside class="growth-preview" aria-label="Vista previa de la oferta">
            <span class="growth-preview-eyebrow"><x-seller-icon name="sparkles"/>Así se presenta tu oferta</span>
            <div class="growth-preview-card">
                <span class="growth-preview-pill" data-preview-discount>Oferta especial</span>
                <h3 data-preview-title>{{ $offer?->title ?? 'Tu próxima gran oferta' }}</h3>
                <p data-preview-body>{{ $offer?->description ?? 'Un mensaje breve y claro invita a tus clientes a preguntar por los detalles.' }}</p>
                <div class="growth-preview-product"><x-seller-icon name="box"/><span data-preview-product>Tu producto seleccionado</span></div>
            </div>
            <p class="seller-help">Vista previa orientativa. Los compradores verán la oferta junto a tu producto cuando esté activa.</p>
        </aside>
    </div>
</form>
