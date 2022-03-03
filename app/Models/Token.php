<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    use HasFactory;
    protected $fillable = ['created_at', 'token', 'username', 'user_type'];

    public function owner(){
        $this->belongsTo(User::class, 'username', 'username');
    }

}