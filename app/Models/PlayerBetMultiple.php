<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerBetMultiple extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'bet_type',
        'coupon_id',
        'amount',
        'status'
    ];
}
