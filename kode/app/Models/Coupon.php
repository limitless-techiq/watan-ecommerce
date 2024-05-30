<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Coupon extends Model
{
    use HasFactory;
    
    public function discount($total)
    { 
   
        $amount = 0;
        
        if($this->type == 1){
            $amount =  ($this->value);
        }elseif($this->type == 2){
            $amount =  (($this->value) / 100 ) * $total;
        }

        if($amount > $total){
            return $total - 1;
        }
        return $amount;
    }

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected static function booted()
    {
        static::creating(function ($coupon) {
            $coupon->uid = str_unique();
        });
    }
}
