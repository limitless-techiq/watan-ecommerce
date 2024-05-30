<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'payment_parameter' => 'object'
    ];


    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }


    public function scopeActive($query)
    {
        return $query->where('status', '1');
    }


    protected static function booted()
    {
        static::creating(function ($paymentMethod) {
            $paymentMethod->uid = str_unique();
        });
    }


    public function scopeSearch($q)
    {
        return $q->when(request()->input('search'),function($q){
            $searchBy = '%'. request()->input('search').'%';
              return $q->where('name','like',$searchBy);
            });
    }
}
