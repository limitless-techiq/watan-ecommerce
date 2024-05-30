<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Seller extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'status',
        'password',
        'image',
        'address',
        'uid'
    ];

    protected static function booted()
    {
        static::creating(function ($seller) {
            $seller->uid = str_unique();
        });
    }

    public function subscription()
    {
        return $this->hasMany(PlanSubscription::class, 'seller_id');
    }


    public function sellerShop()
    {
        return $this->hasOne(SellerShopSetting::class, 'seller_id');
    }

    public function product()
    {
        return $this->hasMany(Product::class, 'seller_id')->withTrashed();
    }

    public function transaction()
    {
        return $this->hasMany(Transaction::class, 'seller_id');
    }

    public function withdraw()
    {
        return $this->hasMany(Withdraw::class, 'seller_id');
    }


    public function ticket()
    {
        return $this->hasMany(SupportTicket::class, 'seller_id');
    }


    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }


    public function scopeBanned($query)
    {
        return $query->where('status', 2);
    }

    public function scopeProductWithTrashed($query, $id){
        return $query->where('seller_id', $id)->withTrashed();
    }

    public function follow()
    {
        return $this->hasMany(Follower::class, 'seller_id');
    }

    protected $casts = [
        'address' => 'object',
    ];


    public function scopeSearch($q)
    {
        return $q->when(request()->input('search'),function($q){
            $searchBy = '%'. request()->input('search').'%';
            return $q->where('name','like',$searchBy)
                        ->orWhere('phone',request()->input('search'))
                        ->orWhere('username',request()->input('search'))
                        ->orWhere('email',request()->input('search'));

            });
    }
}
