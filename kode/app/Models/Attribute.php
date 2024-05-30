<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'status','uid'];

    public function value()
    {
        return $this->hasMany(AttributeValue::class, 'attribute_id');
    }

    protected static function booted()
    {
        static::creating(function ($attribute) {
            $attribute->uid = str_unique();
        });
    }
}
