<section class="buyer-panel account-panel" id="seguridad-cuenta">
    <div class="account-panel-heading"><span class="account-panel-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 15v2" stroke-linecap="round"/></svg></span><h2>Seguridad</h2></div>
    <p>Usa una contraseña de al menos 8 caracteres para proteger tu cuenta.</p>
    @if(session('status') === 'password-updated')<div class="buyer-notice account-success" role="status">Tu contraseña se actualizó correctamente.</div>@endif
    <form method="POST" action="{{ route('password.update') }}" class="buyer-form">
        @csrf @method('PUT')
        <div class="buyer-field"><label for="current_password">Contraseña actual</label><input id="current_password" name="current_password" type="password" required autocomplete="current-password" @if($errors->updatePassword->has('current_password')) aria-invalid="true" aria-describedby="current-password-error" @endif>@if($errors->updatePassword->has('current_password'))<p id="current-password-error" class="buyer-field-error" role="alert">{{ $errors->updatePassword->first('current_password') }}</p>@endif</div>
        <div class="account-fields">
            <div class="buyer-field"><label for="new_password">Nueva contraseña</label><input id="new_password" name="password" type="password" minlength="8" required autocomplete="new-password" @if($errors->updatePassword->has('password')) aria-invalid="true" aria-describedby="new-password-error" @endif>@if($errors->updatePassword->has('password'))<p id="new-password-error" class="buyer-field-error" role="alert">{{ $errors->updatePassword->first('password') }}</p>@endif</div>
            <div class="buyer-field"><label for="password_confirmation">Confirma la nueva contraseña</label><input id="password_confirmation" name="password_confirmation" type="password" minlength="8" required autocomplete="new-password"></div>
        </div>
        <button class="buyer-button" type="submit">Actualizar contraseña</button>
    </form>
</section>
