@php($stateNames = ['publicada' => 'Publicada', 'eliminada' => 'Eliminada', 'aprobada' => 'Aprobada', 'rechazada' => 'Rechazada', 'en_revision' => 'En revisión', 'requiere_correccion' => 'Correcciones', 'pendiente_documentos' => 'Faltan documentos', 'abierto' => 'Abierto', 'resuelto' => 'Resuelto'])
<span class="mod-status {{ $state }}">{{ $stateNames[$state] ?? $state }}</span>
