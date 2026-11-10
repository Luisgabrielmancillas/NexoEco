<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SellerSubscription extends Model
{
    use HasUuids;

    public const PAID_PLANS = ['impulso', 'pro'];

    // Mandate states differ from payment states: an approved payment is not a subscription status.
    public const STATUSES = ['pending', 'authorized', 'paused', 'cancelled'];

    public const ENTITLED_STATUSES = ['authorized', 'cancelled', 'paused'];

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
        return $query->paid()->whereIn('status', self::ENTITLED_STATUSES)->where('paid_until', '>', now());
    }

    public function scopePaid($query)
    {
        return $query->whereIn('plan', self::PAID_PLANS);
    }

    public function entitled(): bool
    {
        return in_array($this->plan, self::PAID_PLANS, true)
            && in_array($this->status, self::ENTITLED_STATUSES, true)
            && (bool) $this->paid_until?->isFuture();
    }

    public function planDetails(): array
    {
        return config('subscriptions.plans.'.$this->plan, []);
    }
}
