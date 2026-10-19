<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MercadoPagoAccount extends Model
{
    protected $guarded = [];

    protected $hidden = ['access_token', 'refresh_token'];

    protected $casts = ['access_token' => 'encrypted', 'refresh_token' => 'encrypted', 'expires_at' => 'datetime', 'live_mode' => 'boolean'];
}
