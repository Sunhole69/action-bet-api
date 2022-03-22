<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerBetCombineAmount extends Model
{
    use HasFactory;
    protected $fillable = [
        'player_bet_combines_id',
        'events_count',
        'amount'
    ];
}
