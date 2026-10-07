@extends('layouts.marketplace')
@section('title', 'Soporte · NexoEco')
@section('content')
<div class="nexo-container"><main class="buyer-page">
    <span class="buyer-eyebrow">Estamos para ayudarte</span><h1>{{ ['preguntas-frecuentes' => 'Preguntas frecuentes', 'ayuda' => 'Centro de ayuda', 'contacto' => 'Contacta con soporte'][$seccion] }}</h1>
    <p class="buyer-description">Encuentra respuestas y administra tus consultas desde tu cuenta.</p>
    <nav class="buyer-tabs" aria-label="Secciones de soporte">
        @foreach(['preguntas-frecuentes' => 'Preguntas frecuentes', 'ayuda' => 'Ayuda', 'contacto' => 'Contacto'] as $clave => $nombre)<a class="{{ $seccion === $clave ? 'active' : '' }}" href="{{ route('comprador.soporte', $clave) }}">{{ $nombre }}</a>@endforeach
    </nav>
    @if($seccion === 'preguntas-frecuentes')
        @foreach([
            '¿Cómo guardo productos y tiendas?' => 'Presiona la estrella de un producto o una tienda. Los encontrarás en Favoritos, separados entre tiendas y productos. Presiona de nuevo la estrella para quitarlos.',
            '¿Dónde puedo consultar mis reseñas?' => 'En Mis opiniones encontrarás las reseñas que has publicado. Puedes filtrarlas por productos o tiendas, editarlas desde su página o eliminarlas.',
            '¿Cómo verifico mi correo?' => 'Introduce el código de 4 dígitos que recibiste por correo. El código vence en 10 minutos. Puedes solicitar uno nuevo desde la pantalla de verificación.',
            '¿Por qué mi cuenta de vendedor está en revisión?' => 'Después de enviar tus datos y documentos, tu solicitud de vendedor queda en revisión. Mientras tanto, puedes utilizar tu cuenta como comprador. El acceso de vendedor se habilita cuando se aprueba tu solicitud.',
            '¿Necesito otra cuenta para vender?' => 'Usa el mismo inicio de sesión para todos los roles. Desde Mi registro de vendedor puedes enviar tus datos y documentos en el registro por pasos.',
        ] as $pregunta => $respuesta)<details class="buyer-panel buyer-faq"><summary>{{ $pregunta }}</summary><p style="margin-top:14px;">{{ $respuesta }}</p></details>@endforeach
        <div class="buyer-notice">¿Tu duda sigue pendiente? <a class="buyer-text-link" href="{{ route('comprador.soporte', 'contacto') }}">Envía una consulta a soporte</a>.</div>
    @elseif($seccion === 'ayuda')
        <div class="buyer-panel"><h2>Encuentra productos y tiendas</h2><p>Busca por nombre, descripción, tienda o categoría en el marketplace. Abre una tarjeta para consultar la información publicada por el vendedor.</p><a class="buyer-text-link" href="{{ route('comprador.dashboard') }}">Explorar el marketplace</a></div>
        <div class="buyer-panel"><h2>Organiza tu selección</h2><p>Guarda tus productos y tiendas con la estrella. Tus favoritos se conservan en tu cuenta para volver a consultarlos.</p><a class="buyer-text-link" href="{{ route('comprador.favoritos', 'productos') }}">Ver mis favoritos</a></div>
        <div class="buyer-panel"><h2>Administra tu cuenta</h2><p>En tu perfil puedes actualizar tus datos y contraseña. Revisa las notificaciones para consultar la actividad de tu cuenta.</p><a class="buyer-text-link" href="{{ route('profile.edit') }}">Abrir mi perfil</a></div>
        <div class="buyer-panel"><h2>Cuéntanos qué necesitas</h2><p>Si encuentras un problema, describe lo ocurrido y el producto o tienda involucrados. No incluyas contraseñas ni datos bancarios.</p><a class="buyer-text-link" href="{{ route('comprador.soporte', 'contacto') }}">Contactar con soporte</a></div>
    @else
        <div class="buyer-panel"><h2>Envía tu consulta</h2><p style="margin-bottom:20px;">Tu mensaje quedará registrado y podrás consultarlo en esta sección.</p>
            <form class="buyer-form" method="POST" action="{{ route('comprador.soporte.store') }}">@csrf
                @if($errors->any())<div class="buyer-notice" role="alert">{{ $errors->first() }}</div>@endif
                <div class="buyer-field"><label for="asunto">Asunto</label><input id="asunto" name="asunto" value="{{ old('asunto') }}" maxlength="150" required placeholder="¿En qué necesitas ayuda?"></div>
                <div class="buyer-field"><label for="mensaje">Mensaje</label><textarea id="mensaje" name="mensaje" minlength="10" maxlength="4000" required placeholder="Describe tu consulta con el mayor detalle posible">{{ old('mensaje') }}</textarea></div>
                <button class="buyer-button" type="submit">Enviar consulta</button>
            </form>
        </div>
        <div class="buyer-section-heading"><h2>Mis consultas</h2></div>
        @forelse($solicitudes as $solicitud)<article class="buyer-panel"><div class="buyer-row"><h3>#{{ $solicitud->id }} · {{ $solicitud->asunto }}</h3><span class="buyer-muted">{{ ['abierta' => 'Recibida', 'en_proceso' => 'En proceso', 'cerrada' => 'Resuelta'][$solicitud->estado] ?? ucfirst($solicitud->estado) }}</span></div><p class="buyer-review-text">{{ $solicitud->mensaje }}</p><div class="buyer-row" style="margin-top:16px;"><span class="buyer-muted">{{ $solicitud->created_at->format('d/m/Y H:i') }} · {{ $solicitud->respuestas_count }} mensajes</span><a class="buyer-text-link" href="{{ route('comprador.soporte.show', $solicitud) }}">Ver conversación y respuestas</a></div></article>@empty<div class="buyer-empty"><h2>Aún no has enviado consultas</h2><p>Usa el formulario para registrar tu primera consulta.</p></div>@endforelse
        <div style="margin-top:24px;">{{ $solicitudes->links() }}</div>
    @endif
</main></div>
@endsection
