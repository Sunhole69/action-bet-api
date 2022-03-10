<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AntePostSportList extends Model
{
    use HasFactory;
    protected $fillable = [
        'sport_id',
        'name',
        'events_count'
    ];
}
