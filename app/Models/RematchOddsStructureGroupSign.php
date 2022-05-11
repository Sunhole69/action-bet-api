<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RematchOddsStructureGroupSign extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function OddsStructureGroup(){
        return $this->hasOne(RematchOddsStructureGroupSign::class, 'adds_structure_group_id');
    }
}
