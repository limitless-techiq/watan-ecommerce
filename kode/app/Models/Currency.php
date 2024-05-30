<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $fillable  = [
        'name',
        'symbol',
        'status',
        'rate',
        'uid'
    ];



    public  function withdraw(){
       return $this->hasMany(WithdrawMethod::class,'currency_id','id');
    }   
    public  function paymentMethods(){
       return $this->hasMany(PaymentMethod::class,'currency_id','id');
    }   
    protected static function booted()
     {
         static::creating(function ($currency) {
             $currency->uid = str_unique();
         });
     }

     public function scopeActive($query)
     {
         return $query->where('status', '1');
     }

     public function scopeDefault($query)
     {
         return $query->where('status', '1');
     }

}
