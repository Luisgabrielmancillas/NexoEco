<section class="buyer-panel account-panel" id="datos-cuenta">
    <div class="account-panel-heading"><span class="account-panel-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="8" r="3.5"/><path d="M5 21v-2a7 7 0 0 1 14 0v2" stroke-linecap="round"/></svg></span><h2>Mis datos</h2></div>
    <p>Actualiza tu nombre y el correo que utilizas para entrar a NexoEco.</p>
    @if(session('status') === 'profile-updated')<div class="buyer-notice account-success" role="status">Tus datos se actualizaron correctamente.</div>@endif
    @if(!$user->hasVerifiedEmail())
        <div class="buyer-notice">
            <h3>Verifica tu correo electrónico</h3>
            <p>Introduce el código de 4 dígitos enviado a tu correo para completar la verificación.</p>
            @if(session('status') === 'verification-code-sent')<p role="status">Enviamos un nuevo código a tu correo.</p>@endif
            @if(session('status') === 'verification-send-failed')<p role="alert">No pudimos enviar el código. Inténtalo de nuevo.</p>@endif
            <div class="buyer-row" style="margin-top:12px;">
                <a class="buyer-button secondary" href="{{ route('verification.notice') }}">Introducir código</a>
                <form method="POST" action="{{ route('verification.send') }}">@csrf<button type="submit" class="buyer-button secondary">Reenviar código</button></form>
            </div>
        </div>
    @endif
    <form method="POST" action="{{ route('profile.update') }}" class="buyer-form">
        @csrf @method('PATCH')
        <div class="account-fields">
            <div class="buyer-field"><label for="name">Nombre completo</label><input id="name" name="name" type="text" value="{{ old('name', $user->nombre_completo ?: $user->name) }}" maxlength="150" required autocomplete="name" @if($errors->has('name')) aria-invalid="true" aria-describedby="name-error" @endif>@error('name')<p id="name-error" class="buyer-field-error" role="alert">{{ $message }}</p>@enderror</div>
            <div class="buyer-field"><label for="email">Correo electrónico</label><input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" maxlength="255" required autocomplete="email" @if($errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif>@error('email')<p id="email-error" class="buyer-field-error" role="alert">{{ $message }}</p>@enderror</div>
        </div>
        <p class="buyer-muted" style="margin-bottom:20px;">Si cambias tu correo, tendrás que verificar la nueva dirección.</p>
        <button class="buyer-button" type="submit">Guardar mis datos</button>
    </form>
</section>
