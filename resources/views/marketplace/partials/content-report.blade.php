@if(auth()->user()?->hasVerifiedEmail())
<details class="buyer-content-report"><summary>Reportar {{ $reportType === 'productos' ? 'publicación' : 'comentario' }}</summary><form method="POST" action="{{ route('comprador.reportes.store', [$reportType, $reportId]) }}" class="buyer-form">@csrf
    <div class="buyer-field"><label for="report-category-{{ $reportType }}-{{ $reportId }}">Motivo del reporte</label><select name="categoria" id="report-category-{{ $reportType }}-{{ $reportId }}" required><option value="">Selecciona un motivo</option><option value="contenido_inapropiado">Contenido inapropiado</option><option value="informacion_falsa">Información falsa</option><option value="spam">Spam</option><option value="otro">Otro</option></select></div>
    <div class="buyer-field"><label for="report-message-{{ $reportType }}-{{ $reportId }}">¿Qué ocurrió?</label><textarea name="motivo" id="report-message-{{ $reportType }}-{{ $reportId }}" required minlength="10" maxlength="2000" rows="3" placeholder="Describe el problema para que podamos revisarlo."></textarea></div><button class="buyer-button secondary" type="submit">Enviar al equipo de moderación</button>
</form></details>
@endif
