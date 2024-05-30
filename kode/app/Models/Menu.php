<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'slug',
        'url',
        'uid',
        'banner_image'
    ];

    public function menuCategory()
    {
        return $this->hasMany(MenuCategory::class, 'menu_id')->orderBy('serial', 'ASC');
    }   

    protected static function booted()
    {
        static::creating(function ($menu) {
            $menu->uid = str_unique();
        });
    }
}
