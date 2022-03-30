<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    use HasFactory;
    protected $dates = ['created_at', 'updated_at', 'token_expiry'];

    protected $fillable = ['created_at', 'token', 'token_expiry', 'username', 'user_type'];

    public function owner(){
        $this->belongsTo(User::class, 'username', 'username');
    }

}
