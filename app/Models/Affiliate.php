<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Affiliate extends Model
{
    use HasFactory;

    public function setPasswordAttribute($value){
        $this->attributes['password'] = bcrypt($value);
    }
}
