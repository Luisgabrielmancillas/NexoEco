<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerAutomation extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['enabled' => 'boolean', 'hours' => 'array'];
}
