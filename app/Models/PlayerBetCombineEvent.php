<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerBetCombineEvent extends Model
{
    use HasFactory;
    protected $fillable = [
        'player_bet_combines_id',
        'search_code',
        'sign_key',
        'rank'
    ];
}
