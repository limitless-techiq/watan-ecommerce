<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected static function booted()
    {
        static::creating(function ($banner) {
            $banner->uid = str_unique();
        });
    }

    /**
     * active banner
     */

     public function scopeActive($q){
        return $q->where('status','1');
     }
}
