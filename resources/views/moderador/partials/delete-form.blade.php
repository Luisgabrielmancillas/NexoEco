@if(!$content->trashed())
<form method="POST" action="{{ route('moderador.contenido.destroy', [$tipo, $content->getKey()]) }}" class="mod-review-form" data-mod-delete data-content-type="{{ $tipo === 'productos' ? 'producto' : 'opinión' }}" data-content-name="{{ $tipo === 'productos' ? $content->nombre_producto : 'Opinión #'.$content->getKey() }}">@csrf @method('DELETE')
    <input type="hidden" name="version" value="{{ $content->revision_contenido }}">
    <label for="reason-{{ $tipo }}-{{ $content->getKey() }}">Motivo de la eliminación</label><textarea id="reason-{{ $tipo }}-{{ $content->getKey() }}" name="motivo" required minlength="10" maxlength="2000" rows="3" placeholder="Explica por qué este contenido es inapropiado."></textarea>
    <small>Se retirará del marketplace y el usuario recibirá el motivo por notificación.</small>
    <button class="mod-button danger" type="submit">Eliminar {{ $tipo === 'productos' ? 'publicación' : 'opinión' }}<x-mod-icon name="close"/></button>
</form>
@endif
