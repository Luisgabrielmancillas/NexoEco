<form method="POST" action="{{ $tipo ? route('tipos-usuario.update', $tipo) : route('tipos-usuario.store') }}" class="seller-form seller-card">
    @csrf @if($tipo) @method('PUT') @endif
    <div class="seller-field"><label for="nombre_tipo">Nombre del tipo de cuenta</label><input id="nombre_tipo" name="nombre_tipo" value="{{ old('nombre_tipo', $tipo?->nombre_tipo) }}" required maxlength="100" @if($errors->has('nombre_tipo')) aria-invalid="true" aria-describedby="nombre-tipo-error" @endif>@error('nombre_tipo')<p id="nombre-tipo-error" class="buyer-field-error">{{ $message }}</p>@enderror</div>
    <p class="seller-help">Los nombres comprador, vendedor, moderador y administrador se utilizan para autorizar el acceso a sus paneles. Crear un tipo nuevo no concede acceso a esos paneles.</p>
    <div class="seller-form-actions"><a class="seller-button secondary" href="{{ route('tipos-usuario.index') }}">Cancelar</a><button class="seller-button" type="submit">{{ $tipo ? 'Guardar cambios' : 'Crear tipo de cuenta' }}</button></div>
</form>
