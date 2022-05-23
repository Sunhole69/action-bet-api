<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RematchOddsStructure extends Model
{
    use HasFactory;
    use HasFactory;
    protected $guarded = [];

    public function groups(){
        return $this->hasMany(RematchOddsStructureGroup::class, 'odds_structure_id');
    }
}
