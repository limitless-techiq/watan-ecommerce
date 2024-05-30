<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Enums\StatusEnum;
class Language extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function booted()
    {
        static::creating(function ($language) {
            $language->uid = str_unique();
        });
    }


     //get created  by info
    public function createdBy(){
        return $this->belongsTo(Admin::class,'updated_by','id');
    }


    // get updated by info
    public function updatedBy(){
        return $this->belongsTo(Admin::class,'updated_by','id');
    }

    //default language
    public function scopeDefault($q){
        return $q->where('is_default',(StatusEnum::true)->status());
    }
    //active language
    public function scopeActive($q){
        return $q->where('status',(StatusEnum::true)->status());
    }
}
