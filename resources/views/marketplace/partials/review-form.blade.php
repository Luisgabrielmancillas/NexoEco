@php($reviewFormId = 'review-'.$tipo.'-'.$item->getKey())
<form method="POST" action="{{ route('comprador.opiniones.store', [$tipo, $item->getKey()]) }}" class="buyer-form review-form">
    @csrf
    @if($reviewErrors)<div class="buyer-notice" role="alert">{{ $errors->first('calificacion') ?: $errors->first('comentario') }}</div>@endif
    <div class="buyer-field"><label for="{{ $reviewFormId }}-rating">Tu calificación</label><select id="{{ $reviewFormId }}-rating" name="calificacion" required><option value="">Selecciona las estrellas</option>@for($rating = 5; $rating >= 1; $rating--)<option value="{{ $rating }}" @selected((int) old('calificacion', $miOpinion?->calificacion) === $rating)>{{ $rating }} {{ $rating === 1 ? 'estrella' : 'estrellas' }}</option>@endfor</select></div>
    <div class="buyer-field"><label for="{{ $reviewFormId }}-comment">Tu experiencia</label><textarea id="{{ $reviewFormId }}-comment" name="comentario" minlength="5" maxlength="2000" required placeholder="Cuéntanos tu experiencia con {{ $tipo === 'productos' ? 'el producto' : 'la tienda' }}">{{ old('comentario', $miOpinion?->comentario) }}</textarea></div>
    <div class="review-form-actions"><button class="buyer-button" type="submit">{{ $editing ? 'Actualizar opinión' : 'Publicar opinión' }}</button>@if($editing)<button class="buyer-button secondary" type="button" data-review-cancel>Cancelar</button>@endif</div>
</form>
