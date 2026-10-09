<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use \Chatify\Traits\InteractsWithChatify;
    use Notifiable;

    public function sendEmailVerificationNotification(): void
    {
        app(\App\Services\EmailVerificationCodeService::class)->send($this);
    }

    protected $table = 'users';

    public function mercadoPagoAccount()
    {
        return $this->hasOne(MercadoPagoAccount::class);
    }

    public function profilePhotoUrl(): ?string
    {
        return $this->profile_photo_path ? route('profile.photo.show', ['user' => $this->id, 'v' => substr(hash('sha256', $this->profile_photo_path), 0, 12)]) : null;
    }

    public function productosFavoritos()
    {
        return $this->belongsToMany(Producto::class, 'productos_favoritos', 'id_usuario', 'id_producto')->withTimestamps();
    }

    public function tiendasFavoritas()
    {
        return $this->belongsToMany(Tienda::class, 'tiendas_favoritas', 'id_usuario', 'id_tienda')->withTimestamps();
    }

    public function opiniones()
    {
        return $this->hasMany(Opinion::class, 'id_usuario');
    }

    public function solicitudesSoporte()
    {
        return $this->hasMany(SolicitudSoporte::class, 'id_usuario');
    }

    protected $casts = [
        'ubicacion_comprador' => 'array',
        'email_verified_at' => 'datetime',
        'fecha_registro' => 'datetime',
        'password' => 'hashed',
        'activo' => 'boolean',
    ];

    protected $hidden = [
        'ubicacion_comprador',
        'password',
        'remember_token',
    ];

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'nombre_completo',
        'fecha_registro',
        'activo',
    ];

    public function pedidos()
    {
        return $this->hasMany(
            Pedido::class,
            'id_comprador'
        );
    }

    public function tiendas()
    {
        return $this->hasMany(
            Tienda::class,
            'id_vendedor'
        );
    }

    public function tipos_usuario()
    {
        return $this->belongsToMany(
            TiposUsuario::class,
            'usuario_tipo',
            'id_usuario',
            'id_tipo_usuario'
        );
    }

    public function solicitudVendedor()
    {
        return $this->hasOne(
            SolicitudVendedor::class,
            'id_usuario'
        );
    }

    public function solicitudesModeradas()
    {
        return $this->hasMany(
            SolicitudVendedor::class,
            'id_moderador'
        );
    }

    public function tieneTipo(string $tipo): bool
    {
        return $this
            ->tipos_usuario()
            ->whereRaw('LOWER(nombre_tipo) = ?', [mb_strtolower(trim($tipo))])
            ->exists();
    }

    public function tieneAlgunTipo(array $tipos): bool
    {
        $tipos = collect($tipos)
            ->map(
                fn ($tipo) => mb_strtolower(
                    trim((string) $tipo)
                )
            )
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($tipos)) {
            return false;
        }

        return $this
            ->tipos_usuario()
            ->whereIn(
                \Illuminate\Support\Facades\DB::raw('LOWER(nombre_tipo)'),
                $tipos
            )
            ->exists();
    }
}
