<?php

namespace App\Models;

use Chatify\Models\Conversation;
use Illuminate\Database\Eloquent\Model;

class ProductChat extends Model
{
    protected $guarded = [];

    public const NEGOTIATION_STATES = ['nuevo' => 'Nuevo interesado', 'negociacion' => 'En negociación', 'entrega' => 'Entrega acordada', 'concretada' => 'Venta concretada', 'no_concretada' => 'No concretada'];

    protected $casts = ['reported_sale_amount' => 'decimal:2', 'sale_reported_at' => 'datetime'];

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

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function messages()
    {
        return $this->hasMany(\Chatify\Models\Message::class, 'conversation_id', 'conversation_id');
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where(fn ($q) => $q->where('buyer_id', $user->id)->orWhere('seller_id', $user->id));
    }
}
