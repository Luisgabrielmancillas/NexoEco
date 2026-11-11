@php($supportNumber = preg_replace('/\D/', '', (string) config('support.whatsapp')))
<aside class="floating-actions" aria-label="Ayuda y novedades">
    @guest
        @if(request()->routeIs('marketplace.index'))
            <button type="button" class="floating-action floating-promo" data-promo-open aria-label="Vende con nosotros" title="Vende con nosotros" aria-haspopup="dialog" aria-controls="vende-con-nosotros-modal" hidden>
                <x-market-icon name="store"/><span>Vende con nosotros</span>
            </button>
        @endif
    @endguest
    @if($supportNumber)
        <a class="floating-action floating-whatsapp" href="https://wa.me/{{ $supportNumber }}?text={{ rawurlencode(config('support.whatsapp_message')) }}" target="_blank" rel="noopener noreferrer" aria-label="Contactar soporte por WhatsApp (abre una pestaña nueva)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M20.5 11.7a8.5 8.5 0 0 1-12.6 7.5L3 20.5l1.3-4.8a8.5 8.5 0 1 1 16.2-4Z"/><path d="M8 7.5c-.6.2-1 1-.8 1.8.7 3 2.6 4.9 5.6 5.7.8.2 1.8-.2 2.2-.9l.3-.8-2-1-1 1c-1.5-.6-2.5-1.6-3.1-3.1l.8-.9-.9-2Z"/></svg>
            <span>Soporte WhatsApp</span>
        </a>
    @endif
</aside>
