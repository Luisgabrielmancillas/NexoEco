@if($tienda->horarios)
<dl class="seller-data-list">@foreach($tienda->horarios as $day => $hours)<div><dt>{{ \App\Http\Requests\StoreBusinessRequest::DAYS[$day] ?? ucfirst($day) }}</dt><dd>{{ !empty($hours['abierto']) ? ($hours['inicio'].' – '.$hours['fin']) : 'Cerrado' }}</dd></div>@endforeach</dl>
@else
<p class="seller-help">El vendedor aún no ha indicado sus horarios.</p>
@endif
