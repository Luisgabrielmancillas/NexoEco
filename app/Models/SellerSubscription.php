<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SellerSubscription extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    protected $attributes = ['currency' => 'MXN', 'status' => 'pending'];

    protected $casts = ['amount' => 'decimal:2', 'paid_until' => 'datetime', 'cancelled_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->belongsToMany(Producto::class, 'product_promotions', 'subscription_id', 'id_producto')->withTimestamps();
    }

    public function pinnedProducts()
    {
        return $this->belongsToMany(Producto::class, 'product_pins', 'subscription_id', 'id_producto')->withTimestamps()->orderBy('product_pins.id');
    }

    public function scopeEntitled($query)
    {
        return $query->whereIn('status', ['authorized', 'cancelled', 'paused'])->where('paid_until', '>', now());
    }

    public function entitled(): bool
    {
        return in_array($this->status, ['authorized', 'cancelled', 'paused']) && $this->paid_until?->isFuture();
    }

    public function planDetails(): array
    {
        return config('subscriptions.plans.'.$this->plan, []);
    }
}
