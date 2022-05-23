<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RematchOddsStructureGroup extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function OddsStructure(){
        return $this->belongsTo(RematchOddsStructure::class, 'odds_structure_id');
    }

    public function signs(){
        return $this->hasMany(RematchOddsStructureGroupSign::class, 'odds_structure_group_id');
    }
}
