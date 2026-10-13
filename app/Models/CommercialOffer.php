<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommercialOffer extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'notified_at' => 'datetime', 'enabled' => 'boolean'];

    public function subscription()
    {
        return $this->belongsTo(SellerSubscription::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function scopeVisible($query)
    {
        return $query->where('enabled', true)->where('starts_at', '<=', now())->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))
            ->whereHas('subscription', fn ($q) => $q->entitled()->whereHas('user', fn ($q) => $q->where('activo', true)->whereHas('tipos_usuario', fn ($q) => $q->whereRaw('LOWER(nombre_tipo) = ?', ['vendedor']))));
    }
}
