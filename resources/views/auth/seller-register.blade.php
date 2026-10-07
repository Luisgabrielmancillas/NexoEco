@extends('layouts.marketplace')
@section('title', 'Regístrate como vendedor - NexoEco')
@include('auth.partials.seller-styles')

@php
    $editable = ! $solicitud || in_array($solicitud->estado, [\App\Models\SolicitudVendedor::ESTADO_PENDIENTE_DOCUMENTOS, \App\Models\SolicitudVendedor::ESTADO_REQUIERE_CORRECCION], true);
    $submitted = $user && (! $editable || $user->tieneTipo('vendedor'));
    $initialStep = $submitted ? 3 : ($user ? 1 : 0);
    if (! $submitted && $errors->any()) {
        foreach ([['nombre_completo', 'email', 'password'], ['rfc', 'curp', 'telefono', 'domicilio_fiscal'], ['identificacion_frente', 'identificacion_reverso', 'constancia_fiscal', 'comprobante_domicilio', 'documentos']] as $index => $fields) {
            if (collect($fields)->contains(fn ($field) => $errors->has($field))) { $initialStep = $index; break; }
        }
    }
@endphp

@section('content')
<div class="seller-page" @unless($submitted) data-seller-wizard data-initial-step="{{ $initialStep }}" data-account-complete="{{ $user ? 'true' : 'false' }}" @endunless>
    <div class="nexo-container">
        <div class="seller-shell">
            <aside class="seller-sidebar">
                <span class="seller-eyebrow">Vende con nosotros</span>
                <h1>Lo que haces merece <span>llegar más lejos.</span></h1>
                <p>Empieza a vender en tu comunidad. Te acompañamos desde tu cuenta hasta la verificación de tus documentos.</p>
                <ol class="seller-steps" aria-label="Pasos del registro">
                    @foreach (['Tu cuenta', 'Datos fiscales', 'Documentos', 'Verificación'] as $index => $label)
                        <li data-step-indicator="{{ $index }}" @if($index === $initialStep) aria-current="step" @endif class="{{ $index < $initialStep ? 'is-complete' : '' }}">
                            <span class="seller-step-number">{{ $index + 1 }}</span><span>{{ $label }}</span>
                        </li>
                    @endforeach
                </ol>
                <div class="seller-security">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                    Tus documentos son privados. Se usan para revisar tu solicitud y no aparecen en tu perfil público.
                </div>
                <p class="seller-footnote">¿Ya tienes cuenta? <a class="seller-link" href="{{ route('login') }}">Inicia sesión</a></p>
            </aside>
            <section class="seller-card">
                @if ($errors->any() && ! $submitted)
                    <div class="seller-notice" role="alert">
                        <strong>Revisa los siguientes datos para continuar:</strong>
                        <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        <span>Por seguridad, vuelve a elegir los archivos y escribe tu contraseña si corresponde.</span>
                    </div>
                @endif
                @if (session('status') === 'verification-code-sent')
                    <div class="seller-notice seller-success" role="status">Enviamos un código de 4 dígitos a tu correo.</div>
                @elseif (session('status') === 'verification-send-failed')
                    <div class="seller-notice" role="alert">Tu información está guardada, pero no pudimos enviar el correo. Intenta reenviarlo con el botón de abajo.</div>
                @elseif (session('status'))
                    <div class="seller-notice seller-success" role="status">{{ session('status') }}</div>
                @endif

                @if ($submitted)
                    @if (! $user->hasVerifiedEmail())
                        @include('auth.partials.email-verification', ['seller' => true])
                    @else
                        <div class="seller-status-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg></div>
                        <span class="seller-eyebrow">Verificación de vendedor</span>
                        @if ($solicitud->estado === \App\Models\SolicitudVendedor::ESTADO_RECHAZADA)
                            <h2>Tu solicitud no fue aprobada</h2>
                            <p class="seller-description">Revisa el resultado de la verificación de tus documentos.</p>
                        @elseif ($solicitud->estado === \App\Models\SolicitudVendedor::ESTADO_APROBADA)
                            <h2>Documentación aprobada</h2>
                            <p class="seller-description">Tu documentación fue aprobada. Tu acceso de vendedor estará disponible cuando se habilite tu cuenta.</p>
                        @else
                            <h2>Tu cuenta de vendedor está en revisión</h2>
                            <p class="seller-description">Tu correo está verificado y recibimos tus documentos. Nuestro equipo revisará tu información antes de habilitarte como vendedor.</p>
                        @endif
                        @if ($solicitud->motivo_revision)
                            <div class="seller-notice">{{ $solicitud->motivo_revision }}</div>
                        @endif
                        <ul class="seller-checklist"><li>Cuenta creada</li><li>Correo verificado</li><li>Datos fiscales y documentos recibidos</li></ul>
                        <p class="seller-footnote">Puedes volver a esta sección para consultar el estado de tu solicitud.</p>
                        <a class="seller-button seller-secondary" href="{{ route('marketplace.index') }}">Explorar NexoEco</a>
                        <form method="POST" action="{{ route('logout') }}" style="margin-top:20px;">@csrf<button class="seller-link" type="submit">Cerrar sesión</button></form>
                    @endif
                @else
                    @if ($solicitud?->estado === \App\Models\SolicitudVendedor::ESTADO_REQUIERE_CORRECCION)
                        <div class="seller-notice"><strong>Necesitamos que corrijas tu solicitud.</strong><br>{{ $solicitud->motivo_revision }}<br>Revisa tus datos y adjunta nuevamente los documentos.</div>
                    @endif
                    <div class="seller-progress" data-wizard-progress hidden aria-live="polite"><span data-progress-label></span><progress max="4" value="{{ $initialStep + 1 }}" aria-label="Avance del registro"></progress></div>
                    <form method="POST" action="{{ $user ? route('vendedor.solicitud.store') : route('vendedor.register') }}" enctype="multipart/form-data" data-wizard-form>
                        @csrf
                        <section data-wizard-step="0">
                            <span class="seller-eyebrow">Paso 01 · Tu cuenta</span>
                            <h2 tabindex="-1">Comencemos por ti</h2>
                            <p class="seller-description">Usarás esta cuenta para seguir tu solicitud y, después de la aprobación, administrar tus ventas.</p>
                            @if ($user)
                                <div class="seller-summary"><strong>{{ $user->nombre_completo ?: $user->name }}</strong><br>{{ $user->email }}<br>Tu cuenta ya está lista. Continúa con tus datos fiscales.</div>
                            @else
                                <div class="seller-field"><label for="nombre_completo">Nombre completo</label><input id="nombre_completo" name="nombre_completo" value="{{ old('nombre_completo') }}" autocomplete="name" maxlength="150" placeholder="Ej. María López" required></div>
                                <div class="seller-field"><label for="email">Correo electrónico</label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="255" placeholder="tu@correo.com" required><span class="seller-help">Aquí recibirás el código de 4 dígitos para verificar tu cuenta.</span></div>
                                <div class="seller-fields-grid">
                                    <div class="seller-field"><label for="password">Contraseña</label><div class="seller-password"><input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required><button type="button" data-seller-password="password" aria-label="Mostrar contraseña">Ver</button></div><span class="seller-help">Usa al menos 8 caracteres.</span></div>
                                    <div class="seller-field"><label for="password_confirmation">Confirmar contraseña</label><div class="seller-password"><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required><button type="button" data-seller-password="password_confirmation" aria-label="Mostrar confirmación de contraseña">Ver</button></div></div>
                                </div>
                            @endif
                            <p class="seller-footnote">¿Ya tienes cuenta de comprador? <a class="seller-link" href="{{ route('login') }}">Inicia sesión y úsala para solicitar acceso como vendedor.</a></p>
                        </section>
                        <section data-wizard-step="1">
                            <span class="seller-eyebrow">Paso 02 · Datos fiscales</span>
                            <h2 tabindex="-1">Cuéntanos sobre tu actividad</h2>
                            <p class="seller-description">Ingresa tus datos como persona física. Deben coincidir con los documentos que adjuntarás en el siguiente paso.</p>
                            <div class="seller-fields-grid">
                                <div class="seller-field"><label for="rfc">RFC</label><input id="rfc" name="rfc" value="{{ old('rfc', $solicitud?->rfc) }}" maxlength="13" pattern="[A-Za-zÑñ&amp;]{3,4}[0-9]{6}[A-Za-z0-9]{3}" title="Ingresa un RFC con formato válido" autocomplete="off" placeholder="Tu RFC" required></div>
                                <div class="seller-field"><label for="curp">CURP</label><input id="curp" name="curp" value="{{ old('curp', $solicitud?->curp) }}" maxlength="18" minlength="18" pattern="[A-Za-z]{4}[0-9]{6}[HhMm][A-Za-z]{5}[A-Za-z0-9][0-9]" title="Ingresa una CURP con formato válido" autocomplete="off" placeholder="Tu CURP" required></div>
                            </div>
                            <div class="seller-field"><label for="telefono">Teléfono de contacto</label><input id="telefono" name="telefono" type="tel" value="{{ old('telefono', $solicitud?->telefono) }}" minlength="10" maxlength="20" autocomplete="tel" placeholder="Ej. 55 1234 5678" required></div>
                            <div class="seller-field"><label for="domicilio_fiscal">Domicilio fiscal</label><textarea id="domicilio_fiscal" name="domicilio_fiscal" minlength="10" maxlength="1000" autocomplete="street-address" placeholder="Calle, número, colonia, ciudad, estado y código postal" required>{{ old('domicilio_fiscal', $solicitud?->domicilio_fiscal) }}</textarea></div>
                        </section>
                        <section data-wizard-step="2">
                            <span class="seller-eyebrow">Paso 03 · Documentos</span>
                            <h2 tabindex="-1">Un paso más para comenzar</h2>
                            <p class="seller-description">Adjunta archivos legibles en JPG, PNG o PDF. Máximo 5 MB por archivo. Después de enviarlos, verificarás tu correo aquí mismo con un código de 4 dígitos.</p>
                            @foreach (['identificacion_frente' => ['Identificación oficial · Frente', 'Frente de tu INE u otra identificación oficial.', true], 'identificacion_reverso' => ['Identificación oficial · Reverso', 'Opcional, si tu identificación tiene información al reverso.', false], 'constancia_fiscal' => ['Constancia de situación fiscal', 'Documento con el RFC y domicilio fiscal que proporcionaste.', true], 'comprobante_domicilio' => ['Comprobante de domicilio', 'Documento legible que muestre tu domicilio.', true]] as $field => [$label, $help, $required])
                                <div class="seller-field seller-upload"><label for="{{ $field }}">{{ $label }} @if($required)<span aria-hidden="true">*</span>@endif</label><span class="seller-help" id="{{ $field }}-help">{{ $help }}</span><input id="{{ $field }}" name="{{ $field }}" type="file" accept=".jpg,.jpeg,.png,.pdf" aria-describedby="{{ $field }}-help" @required($required)></div>
                            @endforeach
                            <div class="seller-summary" data-wizard-summary hidden><h3>Resumen de tu solicitud</h3><span data-summary-name>{{ $user?->nombre_completo ?: $user?->name }}</span><br><span data-summary-email>{{ $user?->email }}</span><br>RFC: <span data-summary-rfc></span></div>
                            <p class="seller-footnote">Al enviar, guardaremos tu cuenta, tus datos y tus documentos. La habilitación como vendedor depende de la revisión de tu solicitud.</p>
                        </section>
                        <div class="seller-actions">
                            <button type="button" class="seller-button seller-secondary" data-wizard-back hidden>← Anterior</button>
                            <button type="button" class="seller-button" data-wizard-next hidden>Continuar →</button>
                            <button type="submit" class="seller-button" data-wizard-submit>{{ $user ? 'Enviar solicitud' : 'Crear cuenta y enviar documentos' }} →</button>
                        </div>
                        <noscript><p class="seller-footnote">Completa los datos y adjunta tus documentos antes de enviar el formulario.</p></noscript>
                    </form>
                @endif
            </section>
        </div>
    </div>
</div>
@endsection
