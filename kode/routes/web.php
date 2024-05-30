<?php

use App\Http\Controllers\CoreController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\WishListController;
use App\Http\Controllers\User\CheckoutController;
use App\Http\Controllers\User\CouponController;
use App\Http\Controllers\User\DigitalProductOrderController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\PaymentMethod\PaymentController;
use App\Http\Controllers\PaymentMethod\StripePaymentController;
use App\Http\Controllers\PaymentMethod\PaypalPaymentController;
use App\Http\Controllers\PaymentMethod\PaystackPayment;
use App\Http\Controllers\PaymentMethod\FlutterwavePaymentController;
use App\Http\Controllers\PaymentMethod\RazorpayPaymentController;
use App\Http\Controllers\CronController;
use App\Http\Controllers\PaymentMethod\BkashController;
use App\Http\Controllers\PaymentMethod\NagadController;
use App\Http\Controllers\PaymentMethod\PaymentWithInstamojo;


use Illuminate\Support\Facades\DB;


    $globalMiddleware = ['sanitizer','maintenance.mode',"dos.security"];
    try {
        DB::connection()->getPdo();
        if(DB::connection()->getDatabaseName()){
            $globalMiddleware = ['sanitizer','maintenance.mode',"dos.security","throttle:refresh"];
        }
    } catch (\Exception $ex) {
        //throw $th;
    }


    Route::middleware( $globalMiddleware)->group(function(){

        Route::middleware(['prevent.back.history'])->group(function(){

                Route::get('cron/run', [CronController::class, 'handle'])->name('cron.run');
                Route::get('/shop', [FrontendController::class, 'shop'])->name('shop')->middleware(['sellerMode.status.check']);
                Route::get('/contact', [FrontendController::class, 'contact'])->name('contact');
                Route::get('/support', [FrontendController::class, 'supportFaq'])->name('support');
                Route::post('/contact/store', [FrontendController::class, 'store'])->name('contact.store');

                /**campaign section start */
                Route::get('/campaigns', [FrontendController::class, 'campaign'])->name('campaign');
                Route::post('/feedback', [FrontendController::class, 'feedback'])->name('feedback.store');
                Route::get('/campaigns/{slug}', [FrontendController::class, 'campaignDetails'])->name('campaign.details');

                Route::get('/compare', [FrontendController::class, 'compare'])->name('compare');
                Route::get('/compare/store', [FrontendController::class, 'compareStore'])->name('compare.store')->middleware('auth');
                Route::get('/compare/delete/{id}', [FrontendController::class, 'compareDelete'])->name('compare.delete');
     
                Route::get('/blog', [FrontendController::class, 'blog'])->name('blog');
                Route::get('/blog/category/{slug}/{id}', [FrontendController::class, 'categoryBlog'])->name('blog.category');
                Route::get('/blog/details/{slug}/{id}', [FrontendController::class, 'blogDetails'])->name('blog.details');


                Route::get('/products', [FrontendController::class, 'product'])->name('product');
                Route::get('/best-selling/products', [FrontendController::class, 'bestProduct'])->name('best.product');
                Route::get('/digital/products', [FrontendController::class, 'digitalProduct'])->name('digital.product');
                Route::get('/todays-deal/products', [FrontendController::class, 'featuredProduct'])->name('todays.deal');
                Route::get('/new-products', [FrontendController::class, 'newProduct'])->name('new.product');
                Route::get('/product/{slug}/{id}/{camp?}', [FrontendController::class, 'productDetails'])->name('product.details');
                Route::get('/digital/product/{slug}/{id}', [FrontendController::class, 'digitalProductDetails'])->name('digital.product.details');
                


                Route::get('/flash-deal/{slug}', [FrontendController::class, 'flashDeal'])->name('flash.deal');

                Route::get('/categories', [FrontendController::class, 'allCategory'])->name('all.category');
                Route::get('/top-category', [FrontendController::class, 'topCategory'])->name('top.category');

                Route::get('/category/{slug}/{id}/{type?}', [FrontendController::class, 'productCategory'])->name('category.product');
                Route::get('/sub/category/{slug}/{sub_category_id}', [FrontendController::class, 'productSubCategory'])->name('category.sub.product');



                //Brand
                Route::get('/brand/{slug}/{brand_id}', [FrontendController::class, 'productBrand'])->name('brand.product');
                Route::get('/brands', [FrontendController::class, 'allBrand'])->name('all.brand');
                Route::get('/top-brands', [FrontendController::class, 'topBrand'])->name('top.brand');
                Route::post('/news/subsribe', [FrontendController::class, 'newsLatterSubscribe'])->name('newslatter.subscribe');
                Route::post('/news/close', [FrontendController::class, 'newsLatterClose'])->name('newslatter.close');

                //Seller
                Route::get('/seller/store/visit/{slug}/{id}',[FrontendController::class, 'sellerStore'])->name('seller.store.visit')->middleware('sellerMode.status.check');
                Route::post('/subscribe', [FrontendController::class, 'subscribe'])->name('subscribe');


                //Language Change
                Route::get('/language/change/{lang?}', [FrontendController::class, 'languageChange'])->name('language.change')->withoutMiddleware(['maintenance.mode']);
                Route::get('/currency/change/{currency?}', [FrontendController::class, 'currencyChange'])->name('currency.change')->withoutMiddleware(['maintenance.mode']);;
                //Quick view
                Route::get('/quick/view/item', [FrontendController::class, 'quickview'])->name('quick.view.item');

                //Cart
                Route::post('cart/store', [CartController::class, 'store'])->name('cart.store');
                Route::post('cart/update', [CartController::class, 'updateCart'])->name('cart.update');
                Route::get('cart/data/get', [CartController::class, 'getCartData'])->name('cart.data.get');
                Route::get('cart/total/item', [CartController::class, 'cartTotalItem'])->name('cart.total.item');
                Route::get('cart/total/amount', [CartController::class, 'totalCartAmount'])->name('cart.total.amount');
                Route::post('cart/delete', [CartController::class, 'delete'])->name('cart.delete');
                Route::get('/view/cart', [CartController::class, 'viewCart'])->name('cart.view');

                Route::get('/product/live-search', [FrontendController::class, 'productLiveSearch'])->name('product.live.search');
                Route::post('/product/stock', [FrontendController::class, 'productStock'])->name('product.stock.price');


                Route::get('/shipping-method', [FrontendController::class, 'shippingMethod'])->name('product.shippingMethod');

                //wish item
                Route::get('/wish/total/item', [WishListController::class, 'wishItemCount'])->name('wish.total.item');
                Route::get('/compare/total/item', [WishListController::class, 'compareItemCount'])->name('compare.total.item');


                Route::get('/get/reviews', [FrontendController::class, 'getProductReview'])->name('get.product.review');


                Route::middleware('auth')->prefix('user')->name('user.')->group(function () {
                    
                    Route::middleware(['checkUserStatus'])->group(function(){

                        Route::any('/dashboard', [UserController::class, 'index'])->name('dashboard');    
                            
                        Route::get('/wish/list/item', [UserController::class, 'wishlistItem'])->name('wishlist.item');
                        Route::get('/reviews', [UserController::class, 'reviews'])->name('reviews');
                        Route::get('/make/payment/{id}', [UserController::class, 'pay'])->name('order.pay');
                        Route::get('/profile', [UserController::class, 'profile'])->name('profile');
                        Route::post('/profile/update', [UserController::class, 'profileUpdate'])->name('profile.update');
                        Route::post('/password/update', [UserController::class, 'passwordUpdate'])->name('password.update');
                        Route::post('/product/review', [UserController::class, 'productReview'])->name('product.review');
                        Route::post('/refund', [UserController::class, 'refund'])->name('refund');
                        Route::post('/subscribe', [UserController::class, 'subscribe'])->name('subscribe');
                        Route::get('/product/attribute/', [UserController::class, 'getProductAttribute'])->name('product.attribute');
                        
                        Route::get('support/tickets', [UserController::class,'supportTicket'])->name('support.ticket.index');
                        Route::post('address/store', [UserController::class,'addressStore'])->name('address.store');
                        Route::post('address/update',[UserController::class,'addressUpdate'])->name('address.update');
                        Route::get('address/delete/{key}',[UserController::class,'addressDelete'])->name('address.delete');

                        Route::get('create/ticket', [SupportTicketController::class, 'create'])->name('support.ticket.create');
                        Route::post('support/ticket', [SupportTicketController::class, 'store'])->name('support.ticket.store');
                        Route::get('support/ticket/view/{ticketNumber}', [SupportTicketController::class, 'view'])->name('support.ticket.view');
                        Route::get('closed/ticket/{ticketNumber}', [SupportTicketController::class, 'closedTicket'])->name('closed.ticket');
                        Route::post('ticket/reply/{id}', [SupportTicketController::class, 'ticketReply'])->name('ticket.reply');
                        Route::get('support/message/delete/{id}', [SupportTicketController::class, 'supportMessageDelete'])->name('support.message.delete');
                        Route::get('support/ticket/file/download/{id}', [SupportTicketController::class, 'supportTicketDownlode'])->name('ticket.file.download');
                        Route::get('/follow/{id}', [UserController::class, 'follow'])->name('follow');
      
                        Route::get('/digital/order/list', [UserController::class, 'digitalOrder'])->name('digital.order.list');
                        Route::get('/order/details/{order_number}', [UserController::class, 'orderDetails'])->name('order.details');
                        Route::post('/delete', [UserController::class, 'deleteOrder'])->name('order.delete');
                        Route::get('/digital/order/details/{order_number}', [UserController::class, 'digitalOrderDetails'])->name('digital.order.details');
                        Route::get('/shopping/cart', [UserController::class, 'shoppingCart'])->name('shopping.cart');
                        Route::get('/product/wish/list', [WishListController::class, 'store'])->name('wish.item.store');
                        Route::post('/product/wish/delete', [WishListController::class, 'delete'])->name('wish.item.delete');

                        Route::get('checkout/{productId?}', [CheckoutController::class, 'checkout'])->name('checkout')->withoutMiddleware(['auth','checkUserStatus'])->middleware(['guest.checkout']);
                        Route::post('order', [CheckoutController::class, 'order'])->name('order')->withoutMiddleware(['auth','checkUserStatus'])->middleware(['guest.checkout']);

                        Route::get('payment/preview', [PaymentController::class, 'preview'])->name('payment.preview')->withoutMiddleware(['auth','checkUserStatus'])->middleware(['guest.checkout']);
                        Route::get('payment/confirm', [PaymentController::class, 'paymentConfirm'])->name('payment.confirm')->withoutMiddleware(['auth','checkUserStatus'])->middleware(['guest.checkout']);


                        Route::post('digital/product/order', [DigitalProductOrderController::class, 'store'])->name('digital.product.order')->withoutMiddleware(['auth','checkUserStatus'])->middleware(['guest.checkout']);

                        Route::get('/track/order/{order_number?}', [UserController::class, 'trackOrder'])->name('track.order')->withoutMiddleware(['auth','checkUserStatus']);

                        
                        Route::post('digital/product/order/cancel', [DigitalProductOrderController::class, 'digitalOrderCancel'])->name('digital.product.order.cancel');

                        Route::post('apply/coupon', [CouponController::class, 'applyCoupon'])->name('apply.coupon');

                    });
                });

                Route::get('/pages/{slug}/{id}', [FrontendController::class, 'websetupMenu'])->name('pages');
                Route::get('/', [FrontendController::class, 'index'])->name('home');

        });

        #bkash 
        Route::controller(BkashController::class)->name('bkash.')->prefix('/bkash')->group(function () {
            Route::get('/payment','payment')->name('payment');
            Route::any('payment/callback/{trx_code?}/{type?}','callBack')->name('callback');
        });

        #nagad 
        Route::controller(NagadController::class)->name('nagad.')->prefix('/nagad')->group(function () {
            Route::get('/payment','payment')->name('payment');
            Route::any('payment/callback/{trx_code?}/{type?}','callBack')->name('callback');
        
        });

        #stripe 
        Route::controller(StripePaymentController::class)->name('stripe.')->prefix('/stripe')->group(function () {
            Route::get('/payment', 'payment')->name('payment');
            Route::any('payment/callback/{trx_code?}/{type?}','callBack')->name('callback');
        });

        #paypal 
        Route::controller(PaypalPaymentController::class)->name('paypal.')->prefix('/paypal')->group(function () {
            Route::get('/payment', 'payment')->name('payment');
            Route::any('payment/callback/{trx_code?}/{type?}','callBack')->name('callback');
        });
    
        #paystack 
        Route::controller(PaystackPayment::class)->name('paystack.')->prefix('/paystack')->group(function () {
            Route::get('/payment', 'payment')->name('payment');
            Route::any('payment/callback/{trx_code?}/{type?}','callBack')->name('callback');
        });

    
        #instamojo 
        Route::controller(PaymentWithInstamojo::class)->name('instamojo.')->prefix('/instamojo')->group(function () {
            Route::get('/payment', 'payment')->name('payment');
            Route::any('payment/callback/{trx_code?}/{type?}','callBack')->name('callback');
        });

        #flutterwave 

        Route::controller(FlutterwavePaymentController::class)->name('flutterwave.')->prefix('/flutterwave')->group(function () {
            Route::get('/payment', 'payment')->name('payment');
            Route::any('payment/callback/{trx_code?}/{type?}','callBack')->name('callback');
        });

        #Razorpay 
        Route::controller(RazorpayPaymentController::class)->name('razorpay.')->prefix('/razorpay')->group(function () {
            Route::get('/payment', 'payment')->name('payment');
            Route::any('payment/callback/{trx_code?}/{type?}','callBack')->name('callback');
        });


        Route::get('/paymnet/success/{trx_number}', [PaymentController::class, 'paymentSuccess'])->name('payment.success');
        Route::get('/paymnet/failed/{trx_number}', [PaymentController::class, 'paymentFailed'])->name('payment.failed');
        Route::get('/order/success/{orderId}', [PaymentController::class, 'orderSuccess'])->name('order.success');

    });


    Route::fallback(function () {
        return view('frontend.error');
    })->name('error')->middleware(["sanitizer"]);

    Route::get('/default/image/{size}', [FrontendController::class, 'defaultImageCreate'])->middleware(["sanitizer"])->name('default.image');

    /** security and captcha */
    Route::controller(CoreController::class)->middleware(["sanitizer"])->group(function () {
        Route::get('/accept-cookie',  'acceptCookie')->name("accept.cookie");
        Route::get('/security-captcha',"security")->name('dos.security');
        Route::post('/security-captcha/verify',"securityVerify")->name('dos.security.verify');
        Route::get('/default-captcha/{randCode}', 'defaultCaptcha')->name('captcha.genarate');
        Route::post('ai-content','aiContent')->name('ai.content');

    });

    Route::get('/language/change/{code}', [FrontendController::class, 'languageChange'])->middleware(['sanitizer'])->name('language.change');

    Route::get('/error/{message?}', function (?string $message = null) {
        abort(403,$message ?? unauthorized_message());
    })->name('error')->middleware(['sanitizer']);
    Route::get('/maintenance-mode', [CoreController::class, 'maintenanceMode'])->name('maintenance.mode')->middleware(['sanitizer']);

    