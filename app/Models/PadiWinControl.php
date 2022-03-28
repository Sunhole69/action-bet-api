<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PadiWinControl extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'available',
        'percentage_bonus'
    ];

    public function updatedBy(){
        return $this->belongsTo(User::class);
    }
}
