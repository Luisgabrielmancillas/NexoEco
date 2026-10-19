<form method="POST" action="{{ route('admin.users.store') }}" class="buyer-form admin-form">
    @php
        $selectedRole = old('rol', request('rol', 'comprador'));
        if ($selectedRole === 'administrador' && $adminCount >= \App\Services\AdministratorLimit::MAX_ACCOUNTS) { $selectedRole = 'comprador'; }
    @endphp
    @csrf
    <div class="admin-form-grid">
        <div class="buyer-field"><label for="new_name">Nombre completo</label><input id="new_name" name="name" value="{{ old('name') }}" maxlength="150" required autocomplete="off"></div>
        <div class="buyer-field"><label for="new_email">Correo electrónico</label><input id="new_email" name="email" type="email" value="{{ old('email') }}" maxlength="255" required autocomplete="off"></div>
        <div class="buyer-field"><label for="new_role">Rol</label><select id="new_role" name="rol" required @if($errors->has('rol')) aria-invalid="true" aria-describedby="new-role-error" @endif>@foreach(['comprador' => 'Comprador', 'moderador' => 'Moderador', 'administrador' => 'Administrador'] as $role => $label)<option value="{{ $role }}" @selected($selectedRole === $role) @disabled($role === 'administrador' && $adminCount >= \App\Services\AdministratorLimit::MAX_ACCOUNTS)>{{ $label }}{{ $role === 'administrador' && $adminCount >= \App\Services\AdministratorLimit::MAX_ACCOUNTS ? ' · Límite alcanzado' : '' }}</option>@endforeach</select><p class="buyer-muted">Administradores: {{ $adminCount }}/{{ \App\Services\AdministratorLimit::MAX_ACCOUNTS }}. Las cuentas desactivadas también cuentan.</p>@error('rol')<p id="new-role-error" class="buyer-field-error" role="alert">{{ $message }}</p>@enderror</div>
        <div class="buyer-field"><label for="new_password">Contraseña inicial</label><input id="new_password" name="password" type="password" minlength="8" maxlength="255" required autocomplete="new-password"><p class="buyer-muted">Mínimo 8 caracteres. Entrega la contraseña al usuario para que pueda entrar.</p></div>
    </div>
    <div class="buyer-notice">La cuenta se crea activa y con el correo verificado. Los vendedores deben enviar sus documentos desde el registro de vendedor.</div>
    <button class="buyer-button" type="submit">Crear cuenta</button>
</form>
