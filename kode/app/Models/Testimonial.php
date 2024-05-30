<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasFactory;


    protected static function booted()
    {
        static::creating(function ($support) {
            $support->uid = str_unique();
        });
    }
}
