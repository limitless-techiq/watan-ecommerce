<?php

namespace App\Providers;

use App\Http\Services\Frontend\FrontendService;
use Illuminate\Support\ServiceProvider;
use App\Models\GeneralSetting;
use App\Models\Currency;
use App\Models\Product;
use App\Models\Withdraw;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Banner;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Seller;
use App\Models\Language;
use App\Models\Frontend;
use App\Models\ExclusiveOffer;
use App\Models\Faq;
use App\Models\FlashDeal;
use App\Models\NewsLatter;
use App\Models\PluginSetting;
use App\Models\Subscriber;
use App\Models\PageSetup;
use App\Models\SupportTicket;
use App\Models\Testimonial;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        Schema::defaultStringLength(191);

        $forntendService =  new FrontendService();
        try {
            Paginator::useBootstrap();
            $general = GeneralSetting::first();
            $view['general'] = GeneralSetting::first();


            $view['openAi']  = $general->open_ai_setting ? json_decode($general->open_ai_setting) : null;
            $view['subscribers'] = Subscriber::latest()->pluck('email');
            $view['newsLatter'] = NewsLatter::first();
            $view['breadcrumb'] = frontend_section('breadcrumb');
            $view['menus'] = Menu::all();

            $view['faqs'] =  Faq::where('status','1')->latest()->get();
            $view['languages']  =  $forntendService->language();
            $view['currencys'] = Currency::where('status', '1')->select('id', 'name', 'rate')->get();
            $view['categories'] = Category::where('status', '1')->whereNull('parent_id')->orderBy('serial', 'ASC')->with(['parent','product','houseProduct','physicalProduct'])->withCount(['parent','product','houseProduct','digitalProduct','physicalProduct'])->take(10)->get();

            $banners = Banner::where('status', '1')->orderBy('serial_id', 'ASC')->get();
            $pageSetups = PageSetup::latest()->orderBy('id', 'ASC')->get();
            
            $view['seller_new_digital_product_count']  =  Product::sellerProduct()->digital()->new()->count();
            $view['seller_new_physical_product_count']  =  Product::sellerProduct()->physical()->new()->count();
            $view['physical_product_order_count']  =  Order::physicalOrder()->inhouseOrder()->placed()->count();
            $view['physical_product_seller_order_count']  =  Order::physicalOrder()->sellerOrder()->placed()->count();
            $view['withdraw_pending_log_count']  =  Withdraw::where('status', '!=', 0)->where('status',2)->count();
            $view['running_ticket']  =  SupportTicket::where('status',1)->count();
            $view['new_products']    =  Product::inhouseProduct()->physical()->new()->inRandomOrder()->with(['review','order','brand','stock','order'])->take(20)->get();


            view()->share($view);

            view()->composer('frontend.partials.seo', function ($view) {
                $view->with([
                    'seo_content' => Frontend::where('slug', 'seo-section')->first(),
                ]);
            });


            view()->composer('frontend.partials.footer', function ($view) use($pageSetups) {
                $view->with([
                    'pageSetups' => $pageSetups,
                ]);
            });

            view()->composer('frontend.section.banner', function ($view) use($banners) {
                $view->with([
                    'banners' => $banners,
                ]);
            });

            $todayDealProducts = Product::where('featured_status','2')->physical()->published()->inRandomOrder()->with(['review','order','stock','order'])->take(8)->get();

            view()->composer('frontend.section.today_deals', function ($view) use ($todayDealProducts) {
                $view->with([
                    'todays_deals_products'=> $todayDealProducts,
                ]);
            });
            $newProducts = Product::inhouseProduct()->physical()->new()->inRandomOrder()->with(['review','order','brand','stock','order'])->take(20)->get();

            view()->composer('frontend.section.new_product', function ($view) use ($newProducts) {
                $view->with([
                    'new_products'=> $newProducts,
                ]);
            });


            view()->composer('frontend.section.digital_product', function ($view) {
                $view->with([
                    'digital_products'=> Product::with(['digitalProductAttribute'])->digital()->whereIn('status',['1','0'])->inRandomOrder()->take(8)->get(),
                ]);
            });
            

            
            view()->composer('frontend.section.flash_deal', function ($view) {
                $now = Carbon::now();
               
                $view->with([
                    'flashDeal' => FlashDeal::where('status','1')
                                        ->where('start_date',"<=",$now)
                                        ->where('end_date',">=",$now)->first()
                ]);
            });


            view()->composer(['frontend.section.top_brand', 'frontend.partials.product_filter','frontend.partials.sidebar'], function ($view) {
                $view->with([
                    'brands'=> Brand::where('status', '1')->where('top', Brand::YES)
                    ->orderBy('serial', 'ASC')
                    ->withCount('houseProduct')
                    ->with(['houseProduct'])
                    ->get(),
                ]);
            });
            view()->composer(['frontend.section.top_category', 'frontend.partials.product_filter'], function ($view) {
                $view->with([
                    'top_categories'=> Category::whereHas('physicalProduct')->with(['houseProduct','product'])->withCount(['product'])->where('status', '1')->where('top', '1')->orderBy('serial', 'ASC')->get(),
                ]);
            });

            view()->composer('frontend.section.best_selling_product', function ($view) {
                $view->with([
                    'best_selling_products'=> Product::with(['gallery','review','order','brand','stock','order'])->physical()->published()->where('best_selling_item_status', '2')->inRandomOrder()->take(6)->get(),
                ]);
            });

            view()->composer('frontend.section.top_product', function ($view) {
                $view->with([
                    'top_products'=> Product::with(['gallery','review','order','brand','stock','order'])->physical()->published()->top()->inRandomOrder()->take(6)->get(),
                ]);
            });
          

            $best_sellers =  Seller::with(['product'=>function($query){
                $query->where('status','1');
            } , 'product.review'])->where('status', 1)
                ->whereHas('sellerShop', function($query){
                    $query->where('status', '1');
                })->whereHas('subscription',function($q){
                    $q->where('status', 1);
                })->where('best_seller_status', 2)->take(10)->with('sellerShop')->get();


            view()->composer('frontend.section.best_seller', function ($view) use( $best_sellers ) {
                $view->with([
                    'bestsellers'=>  $best_sellers  ,
                ]);
            });
            view()->composer('frontend.section.trending_product', function ($view) use( $best_sellers ) {
                $view->with([
                    'bestsellers'=>  $best_sellers,
                ]);
            });


            $testimonials = Testimonial::where('status',1)->latest()->get();

            view()->composer('frontend.section.testimonial', function ($view) use( $testimonials ) {
                $view->with([
                    'testimonials'=>  $testimonials,
                ]);
            });
            view()->composer('auth.login', function ($view) use( $testimonials ) {
                $view->with([
                    'testimonials'=>  $testimonials,
                ]);
            });
        }catch(\Exception $ex) {

        }

    }
}
