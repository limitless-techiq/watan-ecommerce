<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Frontend extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function booted()
    {
        static::creating(function ($frontend) {
            $frontend->uid = str_unique();
        });
    }


    public function scopeActive($query)
    {
        return $query->where('status', '1');
    }



}
