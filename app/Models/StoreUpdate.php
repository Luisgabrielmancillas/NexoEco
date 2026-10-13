<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreUpdate extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['publish_at' => 'datetime', 'published_at' => 'datetime'];

    public function subscription()
    {
        return $this->belongsTo(SellerSubscription::class);
    }

    public function tienda()
    {
        return $this->belongsTo(Tienda::class, 'id_tienda');
    }
}
