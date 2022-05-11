<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SportGroup extends Model
{
    use HasFactory;
    protected $fillable = [
        'sport_id',
        'group_id',
        'name',
        'country_code',
        'events_count',
    ];

    public function leagues(){
        return $this->hasMany(SportLeague::class, 'group_id', 'group_id');
    }
}
