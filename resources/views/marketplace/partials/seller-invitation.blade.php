@guest
<dialog id="vende-con-nosotros-modal" class="seller-invitation" data-seller-invitation aria-labelledby="seller-invitation-title" aria-describedby="seller-invitation-description">
    <button type="button" class="seller-invitation-close" data-promo-close aria-label="Minimizar invitación para vender" autofocus><x-market-icon name="close"/></button>
    <span class="seller-invitation-icon"><x-market-icon name="store"/></span>
    <span class="seller-invitation-eyebrow">Tu negocio, más cerca</span>
    <h2 id="seller-invitation-title">¡Haz crecer tu negocio local!</h2>
    <p id="seller-invitation-description">Únete a NexoEco, publica tus productos y conecta con clientes de tu comunidad.</p>
    <a href="{{ route('vendedor.register') }}" class="buyer-button">Comenzar a vender <span aria-hidden="true">→</span></a>
    <p class="seller-invitation-hint">Puedes cerrar esta ventana y volver desde el botón de la esquina.</p>
</dialog>
@endguest
