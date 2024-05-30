<?php

namespace App\Http\Controllers\api;

use App\Enums\StatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\CartCollection;
use App\Http\Resources\OrderCollection;
use App\Http\Resources\OrderResource;
use App\Http\Resources\PaymentLogResource;
use App\Http\Resources\TicketMessageResource;
use App\Http\Resources\TicketResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\WishlistCollection;
use App\Http\Utility\PaymentInsert;
use App\Http\Services\Frontend\CheckoutService;
use App\Http\Utility\SendMail;
use App\Models\Campaign;
use App\Models\CampaignProduct;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\DigitalProductAttribute;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\PaymentLog;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ShippingDelivery;
use App\Models\SupportFile;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\WishList;
use App\Rules\General\FileExtentionCheckRule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\TicketCollection;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
   
    public $checkoutService;
    public function __construct()
    {
        $this->checkoutService = new CheckoutService();
    }


    /**
     * Get all dashboard data
     *
     * @return JsonResponse
     */
    public function dashboard() : JsonResponse
    {
        $user            = auth()->user();
        
        $wishlists       = Wishlist::with(['product'])
                                    ->latest()
                                    ->where('customer_id', $user->id)
                                    ->paginate(12);

        $carts           = Cart::with(['product'])
                                    ->latest()
                                    ->where('user_id', $user->id)
                                    ->paginate(12);
        $orders          = Order::with(['orderDetails','orderDetails.product'])
                                    ->latest()
                                    ->physicalOrder()->where('customer_id',$user->id)
                                    ->paginate(12);
        $digital_orders  = Order::with(['orderDetails','orderDetails.product'])
                                    ->digitalOrder()
                                    ->latest()
                                    ->where('customer_id',$user->id)
                                    ->paginate(12);
        return api([
            'wishlists'      => new WishlistCollection($wishlists),
            'carts'          => new CartCollection($carts),
            'user'           => new UserResource($user),
            'orders'         => new OrderCollection($orders),
            'digital_orders' => new OrderCollection($digital_orders),
        ])->success(__('response.success'));
    }


     
    public function addressStore(Request $request){

        $rules = [
            'address_name'       => 'required',
            'first_name' => 'required|max:255',
            'last_name'  => 'required|max:255',
            'email'      => 'required|email',
            'phone'      => 'required',
            'address'    => 'required',
            'zip'        => 'required',
            'city'       => 'required',
            'state'      => 'required',
        ];


        $validator = Validator::make($request->all(),$rules);

        if ($validator->fails()){
            return api(['errors'=>$validator->errors()->all()])->fails(__('response.fail'));
        }

       $user            = auth()->user();

       $address =   (array)$user->billing_address;

       $address [t2k($request->input('address_name'))] = $request->except(['_token','address_name']);


       $user->billing_address = $address ;

       $user->save();

       return api([
        'user'           => new UserResource($user),

       ])->success(__('response.success'));

    }



    public function addressUpdate(Request $request){


        $rules = [
            'address_key'        => 'required',
            'address_name'       => 'required',
            'first_name'         => 'required|max:255',
            'last_name'          => 'required|max:255',
            'email'              => 'required|email',
            'phone'              => 'required',
            'address'            => 'required',
            'zip'                => 'required',
            'city'               => 'required',
            'state'              => 'required',
            
        ];

        $validator = Validator::make($request->all(),$rules);

        if ($validator->fails()){
            return api(['errors'=>$validator->errors()->all()])->fails(__('response.fail'));
        }


       $user            = auth()->user();

       $address = (array)$user->billing_address;

       unset(  $address [request()->input('address_key')]);

       $address [t2k($request->input('address_name'))] = $request->except(['_token','address_name','address_key']);
       $user->billing_address = $address ;
       $user->save();

       return api([
        'user'           => new UserResource($user),
       ])->success(__('response.success'));


    }
    

    public function addressDelete(string $key){

       $user            = auth()->user();
       $address = (array)$user->billing_address;

       unset(  $address [$key]);
       $user->billing_address = $address ;
       $user->save();
 
       return api([
        'user'           => new UserResource($user),
       ])->success(__('response.success'));
    }


    /**
     * Get all cart data
     *
     * @return JsonResponse
     */
    public function cart() :JsonResponse {

        $user  = auth()->user();
        $carts = Cart::with(['product'])
                        ->where('user_id', $user->id)
                        ->paginate(12);

        return api([
            'carts'  => new CartCollection($carts),
            'user'   => new UserResource($user),
        ])->success(__('response.success'));

    }



    /**
     * Get all wishlist data
     *
     * @return JsonResponse
     */
    public function wishlistItem() :JsonResponse  {

        $user      = auth()->user();
        $wishlists = Wishlist::with(['product'])->where('customer_id', $user->id)->paginate(12);

        return api([
            'wishlists' => new WishlistCollection($wishlists),
            'user'      => new UserResource($user),
        ])->success(__('response.success'));

    }


    /**
     * Add to cart
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function addtocart(Request $request) :JsonResponse {

        $validator = Validator::make($request->all(), [
            'product_uid' => 'required',
        ]);
        
        if ($validator->fails()) {
            return api(['errors'=> $validator->errors()->all()])->fails('Validation Error');
        }

        $userId       = auth()->user()->id;
        $campaign     = null;
        $product      = Product::with(['stock'])->where('uid',$request->product_uid)->first();

        if(!$product){
            return api(['errors' => ['Product Not found']])->fails(__('response.fail'));
        }

        $atrribute    = @$product->stock->first()->attribute_value;

        if($request->attribute_combination){
           $atrribute = $request->attribute_combination;
        }
    
        $stock =  ProductStock::where('product_id',$product->id)
                                     ->where("attribute_value",$atrribute)
                                     ->first();
        
        $quantity = $request->quantity ??  1;
    

        if($quantity > $product->maximum_purchase_qty){
           return api(['errors'=> ['The maximum should be '.$product->maximum_purchase_qty.' product purchase']])
                              ->fails(__('response.fail'));
        }

        if($quantity > @$stock->qty){
            return api(['errors'=>['Stock Not Available']])
                                ->fails(__('response.fail'));
        }



        $price = $product->discount_percentage > 0 
                    ? round((cal_discount($product->discount_percentage, @$stock->price)))  
                    : (@$stock->price);





        if(request()->campaign_uid) {
            $campaign         = Campaign::where('uid',request()->campaign_uid)->first();
            $productCampaign  = CampaignProduct::where('product_id', $product->id)
                                        ->where('campaign_id', @$campaign->id)
                                        ->first();
            if ($productCampaign) {
                $price = (discount($stock->price, $productCampaign->discount, $productCampaign->discount_type));
            }
        }


        $cart = Cart::where('user_id', $userId)
                        ->where('product_id', $product->id)
                        ->where('attributes_value', $atrribute)
                        ->when($campaign ,function($query) use($campaign){
                            $query->where('campaign_id', $campaign->id);
                        })->first();


      
        if ($cart) {
            if($campaign){

                    if($campaign->id == $cart->campaign_id && $cart->attributes_value == $atrribute ){
                        $quantity =  $cart->quantity + $quantity ;
                        if( $quantity > $product->maximum_purchase_qty || $quantity >$stock->qty  ){
                            return api(['errors'=>['Already Added!! & Maximum product purchase Quantity exceed']])->fails(__('response.fail'));
                        }
                        $cart->quantity = $quantity;
                        $cart->total    =  round($cart->price*$quantity) ;
                        $cart->save();
                    }
                    else{
                        Cart::create([
                            'campaign_id'      => $campaign ? $campaign->id : $cart->campaign_id,
                            'user_id'          => $userId,
                            'session_id'       => null,
                            'product_id'       => $product->id,
                            'price'            => ($price) ,
                            'total'            => (round($price*$quantity)) ,
                            'quantity'         => $quantity ,
                            'attributes_value' => $atrribute
                        ]);
                    }
            }else{

                $quantity =  $cart->quantity + $quantity ;
                if( $quantity > $product->maximum_purchase_qty || $quantity >$stock->qty  ){
                    return api(['errors'=>['Already Added!! & Maximum product purchase Quantity exceed']])->fails(__('response.fail'));
                }
                $cart->quantity = $quantity;
                $cart->total    =  (round($cart->price*$quantity));
                $cart->save();
            }

        } else {
            Cart::create([
                'campaign_id'      => $campaign ? $campaign->id :null,
                'user_id'          => $userId,
                'session_id'       => null,
                'product_id'       => $product->id,
                'price'            => ($price),
                'total'            => (round($price*$quantity)) ,
                'quantity'         => $quantity,
                'attributes_value' => $atrribute
            ]);
        }

        return api(['message'=> translate("Product Added To Cart")])
                             ->success(__('response.success'));

        

    }



    /**
     * Updarte Cart Quantity
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateCart(Request $request): JsonResponse
    {
      

        $validator = Validator::make($request->all(),[
            'uid'      => 'required',
            'quantity' => 'required|integer|min:1',
        ]);


        if ($validator->fails()){
            return api(['errors'=>$validator->errors()->all()])->fails(__('response.fail'));
        }

        $cartItem = Cart::where('uid',$request->uid)->first();
        
        if(!$cartItem){
            return api(['errors'=> [translate("Cart item Not Foundd")]])->fails(__('response.fail'));

        }

        if($request->quantity > $cartItem->product->maximum_purchase_qty){
            return api(['errors'=> ['The maximum should be '.$cartItem->product->maximum_purchase_qty.' product purchase']])->fails(__('response.fail'));

        }

        $cartItem->quantity = $request->quantity;
        $cartItem->total    = $cartItem->price*$request->quantity;
        $cartItem->save();

        return api(['errors'=> [translate("Cart item qty has been updated")]])->success(__('response.fail'));
    
    }


    /**
     * Delete a cart item 
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteCart(Request $request): JsonResponse
    {
       
        $validator = Validator::make($request->all(),[
            'uid' => 'required',
        ]);

        if ($validator->fails()){
            return api(['errors'=>$validator->errors()->all()])->fails(__('response.fail'));
        }

        $cartItem = Cart::where('uid',$request->uid)->first();
        if($cartItem ){

            $cartItem->delete();
            return api(['message' => translate('The product item has been deleted from the cart')])->success(__('response.success'));
        }

        return api(['errors' => [translate('This Cart Items Is Not Available')]])->fails(__('response.fail'));
        
    
    }



    /**
     * Add to Wish list
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function wishlist(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(),[
            'product_uid' => 'required',
        ]);
        if ($validator->fails()){
            return api(['errors'=>$validator->errors()->all()])->fails(__('response.fail'));
        }
        $user     = auth()->user();
        $product  = Product::where('uid', $request->product_uid)->first();
        if(!$product){
            return api(['errors' => [translate('Product does not exist')]])->fails(__('response.fail'));
        }

        $wishlist = WishList::where('customer_id', $user->id)
                                ->where('product_id', $product->id)
                                ->first();

        if($wishlist){
            return api(['errors' => [translate('Item already added to wishlist')]])->fails(__('response.fail'));
        }

        WishList::create([
            'customer_id' => $user->id,
            'product_id' => $product->id
        ]);

        return api(['message' => translate('Item has been added to wishlist')])->success(__('response.success'));

    }



    /**
     * Delete Item form wishlist
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteWishlist(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(),[
            'uid' => 'required',
        ]);

        if ($validator->fails()) {
            return api(['errors'=>$validator->errors()->all()])->fails(__('response.fail'));
        }

        $user     = auth()->user();

        $wishlist = WishList::where('customer_id', $user->id)
                                            ->where('uid', $request->uid)
                                            ->first();
        if($wishlist){
            $wishlist->delete();
            return api(['message' => translate('Item has been Deleted Form Wishlist')])->success(__('response.success'));
        }
   
        return api(['errors' => [translate('Item Not Found')]])->fails(__('response.fail'));
       
    }


    /**
     * Update Profile
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProdile(Request $request): JsonResponse {
        $user = auth()->user();
        $validator = Validator::make($request->all(),[
            'name'       => 'required|max:120',
            'username'   => 'required|unique:users,username,'.$user->id,
            'phone'      => 'required|unique:users,phone,'.$user->id,
            'address'    => 'required|max:250',
            'city'       => 'required|max:250',
            'state'      => 'required|max:250',
            'zip'        => 'required|max:250',
            'image'      => 'nullable|image',
        ]);

        if ($validator->fails()){
            return api(['errors'=>$validator->errors()->all()])->fails(__('response.fail'));
        }

        $user->name       = $request->name;
        $user->username   = $request->username;
        $user->phone      = $request->phone;
        $address = [
            'address' => $request->address,
            'city'    => $request->city,
            'state'   => $request->state,
            'zip'     => $request->zip
        ];

        if($request->hasFile('image')){
            try{
                $removefile  = $user->image ??null;
                $user->image = store_file($request->image, file_path()['profile']['user']['path'], null, $removefile);
            }catch (\Exception $exp){

            }
        }
        $user->address = $address;
        $user->save();

        return api(['message' => translate('Proile Updated Successfully')])->success(__('response.success'));
      
    }



    /**
     * Update Password
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updatePassword(Request $request): JsonResponse {

        $validator = Validator::make($request->all(),[
            'current_password' => 'nullable',
            'password'         => 'required|confirmed',
        ]);

        if ($validator->fails()){
            return api(['errors'=>$validator->errors()->all()])->fails(__('response.fail'));
        }

        $user = auth()->user();

        if($user->password){
            if(!Hash::check($request->current_password, $user->password)) {
                return api(['errors' => [translate('The password doesnot match')]])->fails(__('response.fail'));
            }
        }

        $user->password = Hash::make($request->password);
        $user->save();
        
        return api(['message' => translate('Password Updated')])->success(__('response.success'));


    }


    /**
     * Order Checkout
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function orderCheckout(Request $request): JsonResponse {

        $user = Auth::guard('api')->user();


        $rules = [
            'address_key' => 'required',
            'shipping_method' => 'required|exists:shipping_deliveries,uid',
            'payment_type' => 'required',
        ];

        if (!$user) {
            unset($rules['address_key']);
            $rules += [
                'items' => 'required|array',
                'email' => ['required','email'],
                'first_name' => ['required', 'max:255'],
                'last_name' => ['required', 'max:255'],
                'phone' => ['required'],
                'address' => ['required'],
                'zip' => ['required'],
                'city' => ['required'],
                'state' => ['required'],
            ];

            if($request->input('create_account') == 1){
                $rules ['email'] = ['required','email', 'unique:users'];
                $rules ['phone'] = ['required', 'unique:users,phone'];
            }
        }


        $validator = Validator::make($request->all(),  $rules);

        if ($validator->fails()){
            return api(['errors'=>$validator->errors()->all()])->fails(__('response.fail'));
        }
        $created = false;
        if(!$user && $request->input('create_account') == 1){
            $created = true;
            $user = User::create([
                'name' => $request->input('first_name'),
                'last_name' => $request->input('last_name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'status' => StatusEnum::true->status(),
                'address' => [
                    'address'   => $request->input('address'),
                    'counntry'  => $request->input('address'),
                    'city'      => $request->input('city'),
                    'zip'       => $request->input('zip'),
                    'state'     => $request->input('state'),
                ]
            ]);
   
           $accessToken = $user->createToken('authToken')->plainTextToken;

        }


    
        $items  =  ($user && !$created)
                            ? Cart::with('product')->where('user_id',$user->id)->get() 
                            : $this->perseList($request->input('items'));



        if($items->count() != 0){

            $calculations = $this->checkoutService->calculate($items);
         
            if($request->coupon_code){

                $now    = Carbon::now();

                $coupon = Coupon::where('code', $request->coupon_code)->where('start_date', '<=', $now)->where('end_date', '>=', $now)->where('status', 1)->first();

                if(!$coupon){
                    return api(
                        [
                            'errors' => [translate('Invalid Coupon Code')],
                        ]
                        )->fails(__('response.fail'));  
                }

                
                $discount     = round(($coupon->discount($calculations['total_cart_amount'])));

                if(  (int) $discount == 0 ){
                    return api(
                        [
                            'errors' => [translate('Sorry, your order total doesnt meet the requirements for this coupon')],
                        ]
                        )->fails(__('response.fail'));  
                }
                $calculations['coupon_amount']     =   $discount;
                
    
            }

            $shippingResponse['shipping_delivery'] = ShippingDelivery::where('uid',$request->shipping_method)->first();


            if($user){
                $billingInfos = (array)@$user->billing_address;
                $billinginfo  = (array)@Arr::get($billingInfos ,$request->address_key ,null) ;
            }
            $shippingResponse['address'] =  ($user && !$created) ?  $billinginfo :  [

                'first_name'   => $request->first_name,
                'last_name'    => $request->last_name,
                'email'        => $request->email,
                'phone'        => $request->phone,
                'address'      => $request->address,
                'zip'          => $request->zip,
                'city'         => $request->city,
                'state'        => $request->state,
            ];

    
            $order = $this->checkoutService->createOrder($request,$calculations,$shippingResponse);

            if($user){
                $order->customer_id = @$user->id;
                $order->save();
            }

            $this->checkoutService->createOrderDetails( $items,$order);
            $this->checkoutService->notifyUser($order);

            if($request->payment_type == 1){

                if($user && !$created) $this->checkoutService->cleanCart($items);
                return api(
                    [ 
                        'message'      => translate('Your order has been submitted'),
                        'order'        => new OrderResource($order),
                        'access_token' => @$accessToken ?? null
                    ])->success(__('response.success'));
              
            }


            $paymentLog = PaymentInsert::paymentCreate($request->payment_type,$order->order_id);

            return api(
                    [
                        'message'     => translate('Order Created'),
                        'order'       => new OrderResource($order),
                        'payment_log' => new PaymentLogResource($paymentLog,$order->order_id),
                        'access_token' => @$accessToken ?? false
                    ]
             )->success(__('response.success'));

        }
    
        return api(
            [
                'errors' => [translate('Cart Items Not Found')],
            ]
            )->fails(__('response.fail'));
        
    }

    public function perseList($items){

        $formattedItems = [];

        foreach($items as $item){

            $uid       = is_array($item) ? $item['uid'] : $item->uid;
            $price     = is_array($item) ? $item['price'] : $item->price;
            $attribute = is_array($item) ? $item['attribute'] : $item->attribute;
            $qty       = is_array($item) ? $item['qty'] : $item->qty;
            $product = Product::where('uid',$uid)->first();
            if($product){
                $cartArr = (object) ([
                    'product_id'       => $product->id,
                    'price'            => $price,
                    'quantity'         =>  $qty,
                    'total'            =>  round($price *  $qty) ,
                    'attributes_value' => $attribute 
                ]);
                array_push($formattedItems ,$cartArr);
                
            }
        }


        return (collect($formattedItems));



    }



    /**
     * Pay now 
     *
     * @param string $orderUid
     * @param string $gateway_code
     * @return JsonResponse
     */
    public function payNow(string $orderUid , string $gateway_code ) :JsonResponse{

        $user           = auth()->user();
      
        $order          = Order::with(['orderDetails'])
                                  ->where("customer_id",   $user->id)
                                  ->where('uid',$orderUid)
                                  ->first();

         
        if(!$order){
            return api(['errors' => ['Order not found']])->fails(__('response.fail'));
        }
        

        $paymentLog     = PaymentLog::with('paymentGateway')->where('order_id', $order->id)
                                    ->where('status',0)
                                    ->delete();

        $paymentLog     = PaymentInsert::paymentCreate($gateway_code,$order->order_id);

        return api(
            [
                'message'     => translate('Order Created'),
                'order'       => new OrderResource($order),
                'payment_log' => new PaymentLogResource($paymentLog,$order->order_id)
            ]
         )->success(__('response.success'));

    }

    
    /**
     * Digital order checkout
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function DigitalOrderCheckout(Request $request): JsonResponse
    {
        $user = Auth::guard('api')->user();

        $rules = [
            'digital_attribute_uid' => 'required',
            'product_uid'           => 'required',
            'payment_type'          => 'required',
        ];

        
        if(!$user){
            $rules['email'] = 'required|email';
        }
        $validator = Validator::make($request->all(),$rules);

        if ($validator->fails()){
            return api(['errors'=>$validator->errors()->all()])->fails(__('response.fail'));
        }

        $product = Product::where('uid', $request->product_uid)->first();

        $digitalProductAttribute = DigitalProductAttribute::where('product_id', $product->id)
                                        ->where('uid', $request->digital_attribute_uid)->where('status', '1')
                                        ->first();
        
        $order = Order::create([
            'customer_id'         => $user?->id,
            'order_id'            => general_setting()->order_prefix.random_number(),
            'amount'              => $digitalProductAttribute->price,
            'payment_type'        => $request->payment_type == 1 ? 1 : 2,
            'payment_status'      => Order::UNPAID,
            'status'              => Order::PLACED,
            'order_type'          => Order::DIGITAL,
            'billing_information' => $request->email ? ['email' => $request->email,'username' => $request->email]: ['email' => $user->email,'username' => $user->name],

        ]);
        
        OrderDetails::create([
            'order_id'                     => $order->id,
            'product_id'                   => $product->id,
            'digital_product_attribute_id' => $digitalProductAttribute->id,
            'quantity'                     => 1,
            'total_price'                  => $digitalProductAttribute->price,
        ]);

        $mailCode = [
            'order_number' => $order->order_id,
        ];
        SendMail::MailNotification($user ?? $order->billing_information ,'DIGITAL_PRODUCT_ORDER',$mailCode);

        if($request->payment_type == 1){

            return api(
                [
                    'message' => translate('Your order has been submitted'),
                    'order'   => new OrderResource($order),
                ])->success(__('response.success'));
           
        }

        $paymentLog = PaymentInsert::paymentCreate($request->payment_type ,$order->order_id);

        return api(
            [
                'message'     => translate('Order Created'),
                'order'       => new OrderResource($order),
                'payment_log' => new PaymentLogResource($paymentLog)
            ]
            )->success(__('response.success'));
        

    }



    /**
     * Ordeer checkout success
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function  checkoutSuccess(Request $request) :JsonResponse{
      
        $validator = Validator::make($request->all(),[
            'trx_id'       => 'required',
            'status'       => 'required',
            'payment_data' => 'required_if:status,success',
        ]);

        if ($validator->fails()){
            return api(['errors'=>$validator->errors()->all()])->fails(__('response.fail'));
        }
      
        $paymentLog = PaymentLog::where('trx_number', $request->trx_id)->first();

        if( $paymentLog ){
            if($request->status =='success'){
                Order::where('order_id',$request->trx_id)->update([
                    'payment_info'=>   $request->payment_data
                ]);
                PaymentInsert::paymentUpdate($request->trx_id);
                return api(
                    [
                        'message' => translate('Transaction Completed'),
                    ]
                    )->success(__('response.success'));
            }
            elseif($request->status =='cancel' ||  $request->status =='failed'){
    
                if($paymentLog->status == 1) {
                    $paymentLog->status = 3;
                    $paymentLog->save();
                }
    
                return api(
                    [
                        'errors' => [translate('Order Cancel or Failed')],
                    ]
                    )->fails(__('response.fail'));
            }
        }
     
        return api(
            [
                'errors' => [ translate('Invalid Transaction Id') ],
            ]
            )->fails(__('response.fail'));
    
  

    }



    /**
     * Track Order
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function trackOrder(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required',
        ]);


        $order = Order::with(['OrderDetails','OrderDetails.product'])
                                ->where('order_id', $request->order_id)
                                ->first();

        if(!$order){
            return api(
                [
                    'errors' => [translate('Invalid OrderId')],
                ]
            )->fails(__('response.fail'));
        }

        return api(
        [
            'message' => translate('Order founded'),
            'order' => new OrderResource($order),
        ]
        )->success(__('response.success'));
        
    }
    


  
    /**
     * Get all support tickets
     *
     * @return JsonResponse
     */
     public function supportTicket() :JsonResponse{

        $user     = auth()->user();
        $tickets  = SupportTicket::with(['messages'])->where('user_id', $user->id)
                     ->latest()
                     ->paginate(12);
        return api(
            [
                'tickets' => new TicketCollection($tickets),
            ]
            )->success(__('response.success'));
     }




     /**
      * Get ticket details
      * 
      * @param int | string $ticketId
      * @return JsonResponse
      */
     public function ticketDetails(string | int $ticketId) :JsonResponse{

        
        $user     = auth()->user();

        $ticket   = SupportTicket::with(['messages','messages.supportfiles'])
                        ->where('user_id', $user->id)
                        ->where('ticket_number',$ticketId)
                        ->first();


        return api(
            [
                'ticket'          => new TicketResource($ticket),
                'ticket_messages' => TicketMessageResource::collection($ticket->messages),
            ])->success(__('response.success'));
        
     }



     /**
      * Download ticket file
      *
      * @param int | string $id
      *
      * @return JsonResponse
      */
     public function supportTicketDownlod(string | int $id) :JsonResponse{

        $supportFile = SupportFile::find(($id));

        if(!$supportFile){
            return api(['errors' => ['File not found']])->fails(__('response.fail'));
        }

        $file        = @$supportFile->file;
   

        return api(
            [
                'url'   => @show_image(file_path()['ticket']['path'].'/'.$file)
            ])->success(__('response.success'));

     }


     /**
      * Close a ticket 
      *
      * @param int | string $ticketId
      *
      * @return JsonResponse
      */
     public function closedTicket(string | int $ticketId) :JsonResponse{

        $supportTicket          =  SupportTicket::where('ticket_number',$ticketId)->first();
        if(!$supportTicket){
            return api(['errors' => ['Ticket not found']])->fails(__('response.fail'));
    
        }
        $supportTicket->status  = 4;
        $supportTicket->save();
   
        return api(
            [
                'message'   => 'Ticket Cloesd'
            ])->success(__('response.success'));

     }



     /**
      * Create a new Ticket 
      *
      * @param Request $request
      * @return JsonResponse
      */
     public function ticketStore(Request $request) :JsonResponse{

        $this->validate($request, [
            
            'name'    => 'required|max:255',
            'email'   => 'required|email',
            'subject' => 'required|max:255',
            'message' => 'required',
            'file.*'  => ["nullable",new FileExtentionCheckRule(['pdf','doc','exel','jpg','jpeg','png','jfif','webp'],'file')]    
        ]);

        $supportTicket                = new SupportTicket();
        $supportTicket->ticket_number = random_number();
        $supportTicket->user_id       = auth()->user()->id ?? null;
        $supportTicket->name          = $request->name;
        $supportTicket->email         = $request->email;
        $supportTicket->subject       = $request->subject;
        $supportTicket->priority      = 2;
        $supportTicket->status        = 1;
        $supportTicket->save();

        $message                      = new SupportMessage();
        $message->support_ticket_id   = $supportTicket->id;
        $message->admin_id            = null;
        $message->message             = $request->message;
        $message->save();

        if($request->hasFile('file')) {
            foreach ($request->file('file') as $file) {
                try {
                    $supportFile                     = new SupportFile();
                    $supportFile->support_message_id = $message->id;
                    $supportFile->file               = upload_new_file($file, file_path()['ticket']['path']);
                    $supportFile->save();
                } catch (\Exception $exp) {
                  
                }
            }
        }
   
        return api(
            [
                'ticket'          => new TicketResource($supportTicket),
                'ticket_messages' => TicketMessageResource::collection($supportTicket->messages),
            ])->success(__('response.success'));

     }

     /**
      * Reply a new Ticket 
      *
      * @param Request $request
      * @return JsonResponse
      */
     public function ticketReply(Request $request ,int | string $ticketNumber) :JsonResponse{

        $request->validate([
            'message'  => 'required',
            'file.*'   => ["nullable", new FileExtentionCheckRule(['pdf','doc','exel','jpg','jpeg','png','jfif','webp'],'file')]    

        ]);
        
        $supportTicket           = SupportTicket::where('ticket_number', $ticketNumber)->first();

        if(!$supportTicket){
            return api(['errors' => ['Ticket not found']])->fails(__('response.fail'));
        
        }

        $supportTicket->user_id  = auth()->user()->id ?? null;
        $supportTicket->status   = 3;
        $supportTicket->save();

        $message                    = new SupportMessage();
        $message->support_ticket_id = $supportTicket->id;
        $message->message           = $request->message;
        $message->save();
        
        if($request->hasFile('file')){

            foreach ($request->file('file') as $file) {   
                    try {
                        $supportFile                     = new SupportFile();
                        $supportFile->support_message_id = $message->id;
                        $supportFile->file               = upload_new_file($file, file_path()['ticket']['path']);
                        $supportFile->save();
                    } catch (\Exception $exp) {
                    
                    }
                }
        }

     
        return api(
            [
                'ticket'           => new TicketResource($supportTicket),
                'ticket_messages'  => TicketMessageResource::collection($supportTicket->messages),
            ])->success(__('response.success'));

     }



     /**
      * Get a Oder details
      
      * @param int | string $order_id
      * 
      */
     public function orderDetails(int | string $order_id) :JsonResponse{

        $order = Order::with(['orderStatus'])->where('uid', $order_id)->first();

        if(!$order){
            return api(['errors' => ['Order not found']])->fails(__('response.fail'));
        }

        return api(
            [
                'order'       => new OrderResource($order),
            ])->success(__('response.success'));

     }
}
