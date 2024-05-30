<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
   use HasFactory;


   protected $guarded = [];


   use HasFactory;

   const NO = 1;
   const YES = 2;

   protected $fillable = [
      'serial',
      'name',
      'logo',
      'status',
      'uid'
   ];


   public function product()
   {
      return $this->hasMany(Product::class, 'brand_id');
   }

   public function houseProduct()
   {
       return $this->hasMany(Product::class, 'brand_id')
           ->where('product_type', '102')
           ->where(function ($query) {
               $query->whereNull('seller_id')
                   ->whereIn('status', [0, 1])
                   ->orWhereNotNull('seller_id')
                   ->whereIn('status', [1]);
           });
   }
   protected static function booted()
   {
       static::creating(function ($brand) {
           $brand->uid = str_unique();
       });
   }
}
