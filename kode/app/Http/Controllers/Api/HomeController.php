<?php

namespace App\Http\Controllers\Api;

use App\Enums\BrandStatus;
use App\Enums\CategoryStatus;
use App\Enums\ProductFeaturedStatus;
use App\Enums\ProductStatus;
use App\Enums\ProductSuggestedStatus;
use App\Enums\ProductType;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Resources\BannerCollection;
use App\Http\Resources\BrandCollection;
use App\Http\Resources\BrandResource;
use App\Http\Resources\CampaignCollection;
use App\Http\Resources\CampaignResource;
use App\Http\Resources\CategoryCollection;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CouponCollection;
use App\Http\Resources\CurrencyCollection;
use App\Http\Resources\DigitalProductCollection;
use App\Http\Resources\FlashDealResource;
use App\Http\Resources\FrontendCollection;
use App\Http\Resources\HomeCategoryCollection;
use App\Http\Resources\LanguageCollection;
use App\Http\Resources\LanguageResource;
use App\Http\Resources\PagesCollection;
use App\Http\Resources\PaymentMethodCollection;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\SellerCollection;
use App\Http\Resources\SellerResource;
use App\Http\Resources\SettingResource;
use App\Http\Resources\ShippingCollection;
use App\Models\Banner;
use Carbon\Carbon;
use App\Models\Brand;
use App\Models\Campaign;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\FlashDeal;
use App\Models\Follower;
use App\Models\Frontend;
use App\Models\Language;
use App\Models\MenuCategory;
use App\Models\PageSetup;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Seller;
use App\Models\ShippingDelivery;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $categories       = Category::where('status', CategoryStatus::ACTIVE)->get();

        $brands           = Brand::where('status', BrandStatus::ACTIVE)->get();

        $todayDeals       = Product::with(['review','campaigns'])
                                    ->where('featured_status', ProductFeaturedStatus::YES)
                                    ->where('product_type', ProductType::PHYSICAL_PRODUCT)
                                    ->paginate(paginate_number(12));
        
        $banners          = Banner::active()->get();

        $newArrival       = Product::inhouseProduct()->with(['review','campaigns'])->where('status', ProductStatus::NEW)
                                    ->where('product_type', ProductType::PHYSICAL_PRODUCT)
                                    ->paginate(paginate_number(12));

        $bestSelling      = Product::with(['review','campaigns'])
                                    ->where(function ($query) {
                                        $query->whereNull('seller_id')
                                            ->whereIn('status', [0, 1])
                                            ->orWhereNotNull('seller_id')
                                            ->whereIn('status', [1]);
                                    })
                                    ->where('product_type', ProductType::PHYSICAL_PRODUCT)->where('best_selling_item_status',ProductStatus::BESTSELLING)
                                    ->paginate(paginate_number(12));

        $digital_products = Product::with(['digitalProductAttribute'])
                                    ->digital()
                                    ->where(function ($query) {
                                        $query->whereNull('seller_id')
                                            ->whereIn('status', [0, 1])
                                            ->orWhereNotNull('seller_id')
                                            ->whereIn('status', [1]);
                                    })->latest()
                                    ->whereHas('category', function($query){
                                        $query->where('status', '1');
                                    })->paginate(paginate_number(12));



        $suggestedProducts = Product::with(['review','campaigns'])->whereIn('status', [ProductStatus::NEW, ProductStatus::PUBLISHED])
                                    ->where('is_suggested', ProductSuggestedStatus::YES)
                                    ->where('product_type', ProductType::PHYSICAL_PRODUCT)
                                    ->paginate(paginate_number(12));
        
        $now              = Carbon::now()->toDateTimeString();
        $campaigns        = Campaign::with('products')->where('status',Status::ACTIVE)
                                    ->where('start_time',"<=",$now)
                                    ->where('end_time',">=",$now)->paginate(paginate_number(12));
        
        $sellers          = Seller::active()
                                ->whereHas('sellerShop', function($query){
                                    $query->where('status', 1);
                                })->whereHas('subscription',function($q){
                                    $q->where('status', 1);
                                })->with('sellerShop')->paginate(paginate_number(12));


        
        $flashDeal      = FlashDeal::where('status',Status::ACTIVE)
                                ->where('start_date',"<=",$now)
                                ->where('end_date',">=",$now)
                                ->first();
    
   
        $homeCategories = MenuCategory::whereHas('category',function($q){
            $q->whereHas('product');
        })->with(['category','category.product'])->orderBy('serial')->get();

        return api([
            'shops'              => new SellerCollection($sellers),
            'banners'            => new BannerCollection($banners),
            'categories'         => new CategoryCollection($categories),
            'brands'             => new BrandCollection($brands),
            'today_deals'        => new ProductCollection($todayDeals),
            'suggested_products' => new ProductCollection($suggestedProducts),
            'new_arrival'        => new ProductCollection($newArrival),
            'best_selling'       => new ProductCollection($bestSelling),
            'campaigns'          => new CampaignCollection($campaigns),
            'digital_product'    => new DigitalProductCollection($digital_products),
            'flash_deals'        => $flashDeal ? new FlashDealResource($flashDeal) : (object)[],
            'home_category'      => new HomeCategoryCollection($homeCategories), 
        ])->success(__('response.success'));
        
    }

    /**
     * Get all brand products
     * @param string $uid
     * @return JsonResponse
     */
    public function brandProduct(string $uid): JsonResponse
    {

        $brand    = Brand::where('status', BrandStatus::ACTIVE)->where('uid', $uid)->first();
        if(!$brand){
            return api(['errors' => ['Brand not found']])->fails(__('response.fail'));
        }

        $products = Product::where(function ($query) {
                            $query->whereNull('seller_id')
                                ->whereIn('status', [0, 1])
                                ->orWhereNotNull('seller_id')
                                ->whereIn('status', [1]);
                        })
                        ->where('product_type', ProductType::PHYSICAL_PRODUCT)
                        ->where('brand_id', $brand->id)->paginate(paginate_number(12));

        return api([
            'brand'    => new BrandResource($brand),
            'products' => new ProductCollection($products),
        ])->success(__('response.success'));
    }


    /**
     * Get all Category product
     * 
     * @param string $uid
     * @return JsonResponse
     */
    public function getCategoryByProduct(string $uid): JsonResponse
    {
        $category = Category::where('status', CategoryStatus::ACTIVE)
                                                ->where('uid', $uid)
                                                ->first();
        if(!$category){
            return api(['errors' => ['Category not found']])->fails(__('response.fail'));
        }

        $products = Product::where(function ($query) {
                            $query->whereNull('seller_id')
                                    ->whereIn('status', [0, 1])
                                    ->orWhereNotNull('seller_id')
                                    ->whereIn('status', [1]);
                            })
                            ->where('product_type', ProductType::PHYSICAL_PRODUCT)
                            ->where('category_id', $category->id)->orWhere('sub_category_id',$category->id)->paginate(paginate_number(12));

        return api([
            'category' => new CategoryResource($category),
            'products' => new ProductCollection($products),
        ])->success(__('response.success'));
    }


    /**
     * Campagin details
     * 
     * @param string $uid
     * @return JsonResponse
     */

     public function campaignDetails(string $uid): JsonResponse  {

         $campaign = Campaign::with(['products'])
                       ->where('status',Status::ACTIVE)
                       ->where('uid', $uid)->first();
         if(!$campaign){
            return api(['errors' => ['Camaign Not found']])->fails(__('response.fail'));

         }
         return api([
             'campaign' => new CampaignResource($campaign),
             'products' => new ProductCollection($campaign->products()->paginate(paginate_number(12))),
         ])->success(__('response.success'));
     }





     
     /**
      * Get all configuration
      *
      * @return JsonResponse
      */
      public function config(): JsonResponse {

        $paymentMethods     = PaymentMethod::with(['currency'])->active()->get();
        $languages          = Language::active()->get();
        $defaultLanguage    = Language::default()->first();
        $currencies         = Currency::active()->get();
        $frontends          = Frontend::active()->get();
        $now                = Carbon::now();
        $coupons            = Coupon::where('start_date', '<=', $now)
                                        ->where('end_date', '>=', $now)
                                        ->where('status', '1')->get();

        $ShippingDeliveries =  ShippingDelivery::where('status', 1)
                                        ->orderBy('id', 'DESC')->with('method')
                                        ->get();

        $pages              = PageSetup::where('status','1')->get();

          return api([
            'settings'         => new SettingResource(general_setting()),
            'pages'            => new PagesCollection($pages),
            'payment_methods'  => new PaymentMethodCollection($paymentMethods),
            'languages'        => new LanguageCollection($languages),
            'default_language' => new LanguageResource($defaultLanguage),
            'currency'         => new CurrencyCollection($currencies),
            'coupons'          => new CouponCollection($coupons),
            'frontend_section' => new FrontendCollection($frontends),
            'shipping_data'    => new ShippingCollection($ShippingDeliveries)
          ])->success(__('response.success'));
      }

      
      /**
       * translate a static  word
       */
      public function translate($keyword):JsonResponse{

        return api([
            'keyword' => translate($keyword),
        ])->success(__('response.success'));
      }



    /**
     * Seller shop api
     *
     * @return JsonResponse
     */
    public function shop() :JsonResponse {
        
        $sellers = Seller::active()
                        ->whereHas('sellerShop', function($query){
                            $query->where('status', 1);
                        })->whereHas('subscription',function($q){
                            $q->where('status', 1);
                        })->with('sellerShop')->latest()->get();

  
        return api([
            'shops'    => new SellerCollection($sellers),
        ])->success(__('response.success'));

    }


 
      
    /**
     * Seller shop visit
     * 
     * @param int |string $id
     *
     * @return JsonResponse
     */
    public function shopVisit(int | string $id) :JsonResponse {

        $seller            = Seller::with(['product'])
                                    ->active()
                                    ->whereHas('sellerShop')
                                    ->where('id', $id)->first();

        if(!$seller){
            return api(['errors' => ['Shop Not found']])->fails(__('response.fail'));
         
        }

        $sellers           = Seller::active()
                                    ->latest()
                                    ->whereHas('sellerShop', function($query){
                                        $query->where('status', '1');
                                    })->whereHas('subscription',function($q){
                                        $q->where('status', '1');
                                    })->with('sellerShop')->where('id','!=',$id)->take(10)->get();
        

        $products          = Product::with(['seller','brand', 'rating', 'order'])
                                    ->latest()
                                    ->where('seller_id',$id)
                                    ->whereIn('status', [ProductStatus::PUBLISHED])
                                    ->where('product_type', '102')
                                    ->whereHas('category', function($query){
                                        $query->where('status', '1'); })
                                    ->paginate(paginate_number(12));

        $digital__products =  Product::with(['seller','brand', 'rating', 'order'])
                                    ->latest()
                                    ->where('seller_id',$id)
                                    ->whereIn('status', [ProductStatus::PUBLISHED])
                                    ->where('product_type', '101')
                                    ->paginate(paginate_number(12));

        return api([
            'shop'             => new SellerResource($seller),
            'related_shops'    => new SellerCollection($sellers),
            'products'         => (new ProductCollection($products)),
            'digital_products' => new DigitalProductCollection($digital__products),
        ])->success(__('response.success'));

    }


      /**
       * Seller shop api
       *
       * @param int |string $shopId
       * 
       * @return JsonResponse
       */
      public function shopFollow(int | string $shopId) :JsonResponse {
  
        $customer = Auth()->user();
        $seller   = Seller::where('id', $shopId)->where('status', 1)
                            ->first();
        $follow   = Follower::where('following_id', $customer->id)->where('seller_id', $seller->id)
                            ->first();

        $messsage = translate('Unfollowed Successfully');

        if($follow){
            $follow->delete();
        }else{
            $follow                = new Follower();
            $follow->following_id  = $customer->id;
            $follow->seller_id     = $seller->id;
            $follow->save();
            $messsage = translate('Followed Successfully');
        }
  
          return api([
            'message' =>  $messsage,
          ])->success(__('response.success'));
  
      }

     


}
