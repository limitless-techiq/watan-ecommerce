<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $casts = [
        'shipping_country' => 'object',
        'meta_keywords' => 'object',
        'rating' => 'json',
    ];

    protected $dates = ['deleted_at'];

    const NEW = 0;
    const PUBLISHED = 1;
    const INACTIVE = 2;

    //Product Type
    const DIGITAL = 101;
    const PHYSICAL = 102;

    //Top Product Status
    const NO = '1';
    const YES = '2';

    protected $fillable = [
        'product_type',
        'seller_id',
        'brand_id',
        'category_id',
        'sub_category_id',
        'name',
        'slug',
        'price',
        'discount',
        'discount_percentage',
        'featured_image',
        'short_description',
        'description',
        'warranty_policy',
        'minimum_purchase_qty',
        'maximum_purchase_qty',
        'featured_status',
        'top_status',
        'best_selling_item_status',
        'status',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'meta_image',
        'rating',
        'uid'
    ];
    protected static function booted()
    {
        static::creating(function ($product) {
            $product->uid = str_unique();
        });
    }
    public function scopeFeatured($query)
    {
        return $query->where('featured_status', self::YES);
    }

    public function scopeTop($query)
    {
        return $query->where('top_status', self::YES);
    }

    public function scopeNew($query)
    {
        return $query->where('status', self::NEW);
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::PUBLISHED);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', self::INACTIVE);
    }

    public function scopeInhouseProduct($query)
    {
        return $query->whereNull('seller_id');
    }

    public function scopeSellerProduct($query)
    {
        return $query->whereNotNull('seller_id');
    }

    public function scopeDigital($query)
    {
        return $query->where('product_type', self::DIGITAL);
    }

    public function scopePhysical($query)
    {
        return $query->where('product_type', self::PHYSICAL);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subCategory()
    {
        return $this->belongsTo(Category::class, 'sub_category_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function gallery()
    {
        return $this->hasMany(ProductImage::class, 'product_id','id');
    }

    public function stock()
    {
        return $this->hasMany(ProductStock::class, 'product_id')->with('attribute');
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }

    public function order()
    {
        return $this->hasMany(OrderDetails::class, 'product_id');
    }


    public function digitalProductAttribute()
    {
        return $this->hasMany(DigitalProductAttribute::class, 'product_id');
    }

    public function rating()
    {
        return $this->hasMany(ProductRating::class, 'product_id');
    }
    public function review()
    {
        return $this->hasMany(ProductRating::class, 'product_id','id');
    }
    public function wishlist()
    {
        return $this->hasMany(WishList::class, 'product_id');
    }

    public function shippingDelivery()
    {
        return $this->hasMany(ProductShippingDelivery::class, 'product_id');
    }
    public function exoffer()
    {
        return $this->hasMany(ExclusiveOffer::class, 'product_id','id');
    }

    public function campaigns(){
      return $this->belongsToMany(Campaign::class,CampaignProduct::class,'product_id','campaign_id')->withPivot(['discount_type','discount']);
    }



    public function scopeSearch($q)
    {
        return $q->when(request()->input('search'),function($q){
            $searchBy = '%'. request()->input('search').'%';
                return $q->where('name','like',$searchBy)
                        ->orWhereHas('category',function($q) use($searchBy){
                            $locale = session()->get('locale','en');
                            return $q->where('name->'.$locale,'like',$searchBy); 
                        })->orWhereHas('brand',function($q) use($searchBy){
                            $locale = session()->get('locale','en');
                            return $q->where('name->'.$locale,'like',$searchBy); 
                        });

            })->when(request()->input('search_max'),function($q){
                return $q->whereBetween('price', [convert_to_base(request()->input('search_min')),convert_to_base(request()->input('search_max'))]);
            })->when(request()->input('sort_by') ,function($query) {
                if(request()->input('sort_by') == "hightolow"){
                    $query->orderByRaw("CASE WHEN discount!=0 THEN discount ELSE price END DESC");
                }
                elseif(request()->input('sort_by') == "lowtohigh"){
                    $query->orderByRaw("CASE WHEN discount!=0 THEN discount ELSE price END ASC");
                }
                else{
                    $query->latest();
                }
            })->when(!request()->input('sort_by') ,function($query) {
                    $query->latest();
            });
    }





}
