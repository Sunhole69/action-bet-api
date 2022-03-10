<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AntePostSportEventList extends Model
{
    use HasFactory;
    protected $fillable = [
        'group_id',
        'search_code',
        'name',
        'startdate',
        'multiplicity',
    ];
}
