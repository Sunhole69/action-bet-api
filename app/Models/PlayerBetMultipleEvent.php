<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerBetMultipleEvent extends Model
{
    use HasFactory;
    protected $fillable = [
      'player_bet_multiples_id',
      'search_code',
      'sign_key',
      'rank'
    ];
}
