<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;


    protected $fillable = [
        'serial',
        'name',
        'parent_id',
        'banner',
        'image_icon',
        'meta_title',
        'meta_description',
        'meta_image',
        'status',
        'top',
        'uid'
    ];

    public function scopeParentCategory($query)
    {
        return $query->whereNull('parent_id');
    }

    public function parentCategory()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
    public function scopeTop($q)
    {
        return $q->where('top','1');
    }

    public function parent()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function product()
    {
        return $this->hasMany(Product::class, 'category_id');
    }
    public function digitalProduct()
    {
        return $this->hasMany(Product::class, 'category_id')->digital();

    }
    public function physicalProduct()
    {
        return $this->hasMany(Product::class, 'category_id')->physical();

    }
    public function houseProduct()
    {
        return $this->hasMany(Product::class, 'category_id')->where('product_type','102')
        ->where(function ($query) {
            $query->whereNull('seller_id')
                ->whereIn('status', [0, 1])
                ->orWhereNotNull('seller_id')
                ->whereIn('status', [1]);
        });
    }
    public function houseSubCateProduct()
    {
        return $this->hasMany(Product::class, 'sub_category_id')->where('product_type','102')
        ->where(function ($query) {
            $query->whereNull('seller_id')
                ->whereIn('status', [0, 1])
                ->orWhereNotNull('seller_id')
                ->whereIn('status', [1]);
        });
    }
    protected static function booted()
    {
        static::creating(function ($category) {
            $category->uid = str_unique();
        });
    }
}
