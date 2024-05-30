<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'method_id',
        'duration',
        'price',
        'description',
        'status',
        'uid'
    ];

    public function method()
    {
        return $this->belongsTo(ShippingMethod::class, 'method_id');
    }
    public function order()
    {
        return $this->hasMany(Order::class, 'shipping_deliverie_id' ,'id');
    }
    protected static function booted()
    {
        static::creating(function ($shippingDelivery) {
            $shippingDelivery->uid = str_unique();
        });
    }
}
