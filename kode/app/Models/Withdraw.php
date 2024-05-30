<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Withdraw extends Model
{
    use HasFactory;

    const SUCCESS = 1;
    const PENDIGN = 2;
    const REJECT = 3;
    protected $guarded = [];

    public function method()
    {
        return $this->belongsTo(WithdrawMethod::class, 'method_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class,'currency_id');
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::SUCCESS);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::PENDIGN);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::REJECT);
    }


    
    public function scopeSearch($q)
    {
        return $q->when(request()->input('search'),function($q){
            $searchBy = '%'. request()->input('search').'%';
            return $q->where('amount',request()->input('search'))->orWhere('trx_number',request()->input('search'))
                      ->orWhereHas('seller',function($q) use($searchBy){
                        return $q->where('name','like',$searchBy)
                           ->Orwhere('email','like',$searchBy)
                           ->Orwhere('username','like',$searchBy);
                      });

            });
    }
   /**
     * Date Filter
     *
     * @param Builder $query
     * @param string $column
     * @return Builder
     */
    public function scopeDate(Builder $query, string $column = 'created_at') : Builder {

        if (!request()->date) {
            return $query;
        }
        $dateRangeString             = request()->date;
        $start_date                  = $dateRangeString;
        $end_date                    = $dateRangeString;
        if (strpos($dateRangeString, ' to ') !== false) {
            list($start_date, $end_date) = explode(" to ", $dateRangeString);
        } 

        return $query->where(function ($query) use ($start_date, $end_date ,$column ) {
            $query->whereBetween($column , [$start_date, $end_date])
                ->orWhereDate($column , $start_date)
                ->orWhereDate($column , $end_date);
        });

    }
}
