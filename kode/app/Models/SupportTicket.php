<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }


    public function scopeSearch($q)
    {
        return $q->when(request()->input('search'),function($q){
            $searchBy = '%'. request()->input('search').'%';
            return $q->where('name','like',$searchBy)
                      ->orWhere('email','like',$searchBy)
                      ->orWhere('subject','like',$searchBy)
                      ->orWhere('ticket_number','like',$searchBy);
                        
            });
    }


    public function messages()
    {
        return $this->hasMany(SupportMessage::class, 'support_ticket_id', 'id')->latest();
    }

    protected static function booted()
    {
        static::creating(function ($supportTicket) {
            $supportTicket->uid = str_unique();
        });
    }
}
