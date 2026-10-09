<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Apartado extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $attributes = ['estado' => 'pendiente', 'provider' => 'mercadopago'];

    protected $casts = ['monto' => 'decimal:2', 'pagado_at' => 'datetime', 'live_mode' => 'boolean'];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto')->withTrashed();
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where(fn ($q) => $q->where('buyer_id', $user->id)->orWhere('seller_id', $user->id));
    }
}
