<section class="buyer-panel account-panel account-danger" id="eliminar-cuenta">
    <h2>Eliminar mi cuenta</h2>
    <p>Esta acción es permanente. Tus favoritos, opiniones y consultas de soporte se eliminarán junto con tu cuenta.</p>
    <details @if($errors->userDeletion->isNotEmpty()) open @endif>
        <summary>Quiero eliminar mi cuenta</summary>
        <form method="POST" action="{{ route('profile.destroy') }}" class="buyer-form">
            @csrf @method('DELETE')
            <div class="buyer-notice">Para confirmar la eliminación de tu cuenta, escribe tu contraseña y presiona “Eliminar mi cuenta definitivamente”.</div>
            <div class="buyer-field"><label for="delete_password">Confirma con tu contraseña</label><input id="delete_password" name="password" type="password" required autocomplete="current-password" @if($errors->userDeletion->has('password')) aria-invalid="true" aria-describedby="delete-password-error" @endif>@if($errors->userDeletion->has('password'))<p id="delete-password-error" class="buyer-field-error" role="alert">{{ $errors->userDeletion->first('password') }}</p>@endif</div>
            <button class="buyer-button danger" type="submit">Eliminar mi cuenta definitivamente</button>
        </form>
    </details>
</section>
