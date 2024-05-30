<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DigitalProductAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'price',
        'short_details',
        'uid'
    ];



    public function digitalProductAttributeValueKey()
    {
        return $this->hasMany(DigitalProductAttributeValue::class, 'digital_product_attribute_id');
    }

    public function attribute()
    {
        return $this->hasMany(Product::class, 'product_id');
    }

    protected static function booted()
    {
        static::creating(function ($digitalProductAttribute) {
            $digitalProductAttribute->uid = str_unique();
        });
    }
}
