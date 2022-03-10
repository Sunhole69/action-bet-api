<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialSportGroup extends Model
{
    use HasFactory;
    protected $fillable = [
        'sport_id',
        'group_id',
        'name',
        'events_count',
    ];
}
