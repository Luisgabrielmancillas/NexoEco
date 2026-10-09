<?php

namespace App\Models;

use Chatify\Models\Conversation;
use Illuminate\Database\Eloquent\Model;

class ProductChat extends Model
{
    protected $guarded = [];

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
