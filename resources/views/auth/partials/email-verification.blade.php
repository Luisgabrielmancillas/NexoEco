<div class="seller-status-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 6 9 7 9-7"/></svg></div>
<span class="seller-eyebrow">{{ $seller ? 'Paso 04 · Verificación' : 'Bienvenido a NexoEco' }}</span>
<h2>Verifica tu correo electrónico</h2>
<p class="seller-description">Ingresa el código de 4 dígitos enviado a <span class="seller-email">{{ auth()->user()->email }}</span> para confirmar tu cuenta.</p>
@if ($seller)
    <div class="seller-summary"><h3>Tu cuenta de vendedor está en revisión</h3>Tu información ya está guardada. Recibimos tus datos y documentos. Al verificar tu correo entrarás al dashboard del comprador mientras revisamos tu solicitud de vendedor.</div>
@endif
<form method="POST" action="{{ route('verification.verify') }}" class="verification-code-form">
    @csrf
    <div class="seller-field">
        <label for="codigo">Código de verificación</label>
        <input id="codigo" name="codigo" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{4}" minlength="4" maxlength="4" value="{{ old('codigo') }}" placeholder="0000" aria-describedby="codigo-help" @if($errors->has('codigo')) aria-invalid="true" @endif required autofocus>
        <span class="seller-help" id="codigo-help">El código vence en 10 minutos. Si solicitas otro, utiliza el más reciente.</span>
        @error('codigo')<div class="seller-notice" role="alert" style="margin-top:12px;">{{ $message }}</div>@enderror
    </div>
    <button type="submit" class="seller-button">Verificar y continuar →</button>
</form>
<p class="seller-footnote">¿No encuentras el correo? Revisa la carpeta de spam o correo no deseado. Si el código venció, solicita uno nuevo.</p>
<div class="seller-actions">
    <form method="POST" action="{{ route('verification.send') }}">@csrf<button type="submit" class="seller-button seller-secondary">Reenviar código</button></form>
    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="seller-link">Cerrar sesión</button></form>
</div>
