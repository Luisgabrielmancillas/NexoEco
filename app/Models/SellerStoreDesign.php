<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerStoreDesign extends Model
{
    protected $guarded = ['id'];

    public const THEMES = ['terracota' => 'Terracota', 'bosque' => 'Bosque', 'oceano' => 'Océano'];
}
