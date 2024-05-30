<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageSetup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'uid'
    ];

    protected static function booted()
    {
        static::creating(function ($pageSetup) {
            $pageSetup->uid = str_unique();
        });
    }
}
