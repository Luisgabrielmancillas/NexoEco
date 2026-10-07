<section class="buyer-panel account-panel">
    <h2>Mi foto de perfil</h2><p>Personaliza tu cuenta con una foto. Puedes cambiarla cuando quieras.</p>
    @if(session('status') === 'photo-updated')<div class="buyer-notice account-success" role="status">Tu foto de perfil se actualizó correctamente.</div>@endif
    @if(session('status') === 'photo-removed')<div class="buyer-notice account-success" role="status">Tu foto de perfil se eliminó.</div>@endif
    <div class="account-photo-row">
        <div class="account-photo-preview">@if($user->profilePhotoUrl())<img src="{{ $user->profilePhotoUrl() }}" alt="Tu foto de perfil" data-photo-preview>@else<img alt="Vista previa de tu foto" data-photo-preview hidden><span data-photo-initials aria-hidden="true">{{ $iniciales }}</span>@endif</div>
        <form method="POST" enctype="multipart/form-data" action="{{ route('profile.photo.store') }}" class="buyer-form" data-photo-form>
            @csrf
            <div class="buyer-field"><label for="foto">Selecciona una foto</label><input id="foto" name="foto" type="file" accept="image/jpeg,image/png,image/webp" required data-photo-input aria-describedby="photo-help"><p id="photo-help" class="buyer-muted">JPG, PNG o WebP · máximo 3 MB.</p>@if($errors->profilePhoto->has('foto'))<p class="buyer-field-error" role="alert">{{ $errors->profilePhoto->first('foto') }}</p>@endif</div>
            <button class="buyer-button" type="submit">Guardar foto</button>
        </form>
    </div>
    @if($user->profile_photo_path)<form method="POST" action="{{ route('profile.photo.destroy') }}" style="margin-top:18px;">@csrf @method('DELETE')<button class="buyer-text-link" type="submit">Quitar foto de perfil</button></form>@endif
</section>
