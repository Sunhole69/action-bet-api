<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgencyBet extends Model
{
    use HasFactory;
    protected $fillable = [
      'user_id',
      'player_username',
      'bet_type',
      'amount',
      'status'
    ];
}
