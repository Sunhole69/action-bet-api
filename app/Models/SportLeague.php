<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SportLeague extends Model
{
    use HasFactory;
    protected $fillable = [
        'group_id',
        'champ_id',
        'name',
        'country_code',
        'events_count',
    ];
}
