<?php

namespace App\Models;

use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
class Country extends Model
{
    use HasFactory ;

    
    protected $guarded = [];


    protected static function booted()
    {
        static::creating(function (Model $model) {
            $model->uid            = Str::uuid();
            $model->is_blocked     = StatusEnum::false->status();
        });


        static::saved(function (Model $model) {
            Cache::forget('countries');

        });

    }


    public function scopeActive(Builder $q) :Builder {
        return $q->where("is_blocked",StatusEnum::false->status());
    }



    public function updatedBy() :BelongsTo{
        return $this->belongsTo(Admin::class,'updated_by','id')->withDefault([
            'username' => 'N/A',
            'name' => 'N/A'
        ]);
    }


    public function ip() :HasMany{
        return $this->hasMany(Visitor::class,'country_id','id');
    }


    public static function insertOrupdtae(array $ipInfo) :mixed{

        $country    = Country::where(DB::raw('LOWER(name)'), '=', strtolower(Arr::get($ipInfo ,"country","")))
                        ->orWhere(DB::raw('LOWER(code)'), '=', strtolower(Arr::get($ipInfo ,"code","")))
                        ->first();
    
        if(!$country &&  Arr::get($ipInfo ,"country",null) != null){
            $country         = new Country();
            $country->name   = Arr::get($ipInfo ,"country","");
            $country->code   = Arr::get($ipInfo ,"code","");
            $country->save();
        }
        return $country;

    }


}
