<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialSportEventList extends Model
{
    use HasFactory;
    protected $fillable = [
        'league_id',
        'search_code',
        'name',
        'startdate',
        'multiplicity',
    ];
}
