<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeAdvertBanner extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function getBannerAttribute(){
        return asset("uploads/images/$this->image");
    }


}
