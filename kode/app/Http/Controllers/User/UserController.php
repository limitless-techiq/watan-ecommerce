<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PaymentMethod\PaymentController;
use App\Http\Services\Frontend\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\WishList;
use App\Models\Product;
use App\Models\Order;
use App\Models\Cart;
use App\Models\ShippingDelivery;
use App\Models\Follower;
use App\Models\ProductRating;
use App\Models\DigitalProductAttribute;
use App\Models\OrderDetails;
use App\Models\PaymentMethod;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\Hash;
use App\Models\Seller;
use App\Models\Attribute;
use App\Models\Refund;
use App\Models\Subscriber;
use App\Models\User;
use App\Http\Services\Frontend\ProductService;
use App\Models\PaymentLog;
use Illuminate\Support\Arr;

class UserController extends Controller
{


    public $userService ,$productService;
    public function __construct()
    {
        $this->userService = new UserService();
        $this->productService = new ProductService();
    }


    
    public function index(Request $request)
    {

        $title = "User Dashboard";
        
        $user = User::with(['follower'=>function($q){
            return $q->with(['seller'=>function($q){
                return $q->with(['sellerShop']);
            }]);
        },'reviews','reviews.product','reviews.product.brand'])->where('id',Auth::user()->id)->first();
      

        $items = $this->productService->getCartItem();

        $orders = Order::with(['refund','log'])->where('customer_id', $user->id)->orderBy('id', 'DESC')->physicalOrder()->latest();
        session()->put('order_search',$request->search_data);
        if($request->search_data && $request->search_data !='all'){
            $date = \Carbon\Carbon::today()->subDays($request->search_data);
            $orders = $orders->where('created_at','>=',$date)->paginate(paginate_number())->appends(request()->all());
        }
        else{
            $orders = $orders->paginate(paginate_number())->appends(request()->all());
        }
        $wishlists = $this->userService->wishlistItems();

        $digitalOrders = Order::where('customer_id', $user->id)->orderBy('id', 'DESC')->digitalOrder()->latest()->paginate(paginate_number());
        return view('user.dashboard', compact('title', 'user', 'orders', 'items', 'digitalOrders','wishlists'));
    }

    public function supportTicket(){

        $user = Auth::user();
        $title = "Support Ticket";
        $supportTickets = SupportTicket::with(['messages'])->where('user_id', $user->id)->latest()->paginate(15);
        return view('user.ticket', compact('title','supportTickets','user'));

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

        $request->validate(    $rules);


       $user    = auth_user('web');

       $address =   (array)$user->billing_address;

       $address [t2k($request->input('address_name'))] = $request->except(['_token','address_name']);


       $user->billing_address = $address ;

       $user->save();

       return back()->with('success',translate('Adress Created'));



    }



    public function addressUpdate(Request $request){


        $rules = [
            'address_name'       => 'required',
            'address_key'       => 'required',
            'first_name'         => 'required|max:255',
            'last_name'          => 'required|max:255',
            'email'              => 'required|email',
            'phone'              => 'required',
            'address'            => 'required',
            'zip'                => 'required',
            'city'               => 'required',
            'state'              => 'required',
            
        ];

        $request->validate(    $rules);


       $user            = auth_user('web');

       $address = (array)$user->billing_address;



       unset(  $address [request()->input('address_key')]);

       $address [t2k($request->input('address_name'))] = $request->except(['_token','address_name','address_key']);
       $user->billing_address = $address ;
       $user->save();

       return back()->with('success',translate('Adress Updated'));


    }
    

    public function addressDelete(string $key){

       $user            = auth_user('web');
       $address = (array)$user->billing_address;

       unset(  $address [$key]);
       $user->billing_address = $address ;
       $user->save();
       return back()->with('success',translate('Adress Deleted'));

    }
    
    


    public function orderDetails($orderNumber)
    {
        $title = "Order Details";
        $user = Auth::user();
        $order = Order::where('customer_id', $user->id)->where('order_id', $orderNumber)->physicalOrder()->first();
        $orderDetails = OrderDetails::where('order_id', $order->id)->with('product', 'product.brand','product.review')->get();
        return view('user.order_details', compact('title', 'orderDetails'));
    }

    public function digitalOrderDetails($orderNumber)
    {
        $title = "Digital order details";
        $user = Auth::user();
        $order = Order::where('customer_id', $user->id)->where('order_id', $orderNumber)->digitalOrder()->first();
        if(!$order){
            return back();
        }
        $orderDetail = OrderDetails::where('order_id', $order->id)->first();
        $digitalProductAttributes = DigitalProductAttribute::where('id', $orderDetail->digital_product_attribute_id)->first();
        return view('user.digital_order_details', compact('title', 'orderDetail', 'digitalProductAttributes'));
    }

    public function wishlistItem()
    {

        $title = "Wishlist items";
        $wishlists = $this->userService->wishlistItems();
        return view('user.wish_list', compact('title', 'wishlists'));
    }

    public function reviews()
    {
        $title   = "All product reviews";
        $user    = Auth::user();

        return view('user.reviews', compact('title', 'user'));
    }

    /**
     * get product attribute
     */
    public function getProductAttribute()
    {
        $productId = request()->get('product_id');

        $product =  Product::with('stock')->where('id',$productId)->first();

        $attributeIds = array_unique($product->stock->pluck('attribute_id')->toArray());

        $attributeInfos = Attribute::with(['value'=>function($q)use($product){
            return $q->whereIn('name',$product->stock->pluck('attribute_value')->toArray());
        }])->whereIn('id',$attributeIds)->get();

        $array = [];
        foreach($attributeInfos as $attributeInfo){
              $array [$attributeInfo->name.'+'.$attributeInfo->id] = $attributeInfo->value->pluck('name')->toArray();
        }
        return json_encode([
            'data'=> $array,
            'productId' => $productId
        ]);
    }

    public function shoppingCart()
    {
        $title = "All shopping cart";
        $user = Auth::user();
        $items = Cart::with(['product','product.brand'])->where('user_id',$user->id)->orWhere('session_id', session()->get('session_id'))->orderBy('id', 'desc')->paginate(paginate_number());
        return view('frontend.view_cart', compact('title', 'items'));
    }

    public function digitalOrder()
    {
        $title = "All digital order list";
        $user = Auth::user();
        $digtal_orders = Order::where('customer_id', $user->id)->digitalOrder()->orderBy('id', 'DESC')->paginate(paginate_number());
        return view('user.digtal_order', compact('title', 'digtal_orders','user'));
    }

    public function followShop()
    {
        $title = "Follow shops";
        $user = User::with(['follower'=>function($q){
            return $q->with(['seller'=>function($q){
                return $q->with(['sellerShop']);
            }]);
        }])->where('id',Auth::user()->id)->first();
        return view('user.follow_shop', compact('title', 'user'));
    }

    public function trackOrder($id =null)
    {
        $title = "Tracking Order";
        $user = Auth::user();
        $order = null;
        $orderNumber = request()->input('order_number');
        if($id){
            $orderNumber = $id;
        }
        if($orderNumber){
            $order = Order::with(['OrderDetails','OrderDetails.product'])->where('order_id', $orderNumber)->first();
            if(!$order){
                return back()->with('error', translate("Invalid Order"));
            }
        }
        return view('frontend.track_order', compact('title', 'user', 'order', 'orderNumber'));
    }

    public function follow($id)
    {

        $seller = Seller::where('id', $id)->where('status', 1)->firstOrFail();
        $customer = Auth()->user();
        $follow = Follower::where('following_id', $customer->id)->where('seller_id', $seller->id)->first();

        if($follow){
            $follow->delete();
            return back()->with('success', translate("Unfollowed Successfully"));
        }else{
            $follow = new Follower();
            $follow->following_id = $customer->id;
            $follow->seller_id = $seller->id;
            $follow->save();
            return back()->with('success', translate("Followed Successfully"));
        }
    }

  

    public function profile(Request $request)
    {

        $user = Auth::user();
        $title = "Profile";
        return view('user.profile', compact('title','user'));

    }


    public function profileUpdate(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => 'required|max:120',
            'username' => 'required|unique:users,username,'.$user->id,
            'phone' => 'required|unique:users,phone,'.$user->id,
            'address' => 'required|max:250',
            'city' => 'required|max:250',
            'state' => 'required|max:250',
            'zip' => 'required|max:250',
        ]);
        $user->name = $request->name;
        $user->last_name = $request->last_name;
        $user->username = $request->username;
        $user->phone = $request->phone;
        $address = [
            'address' => $request->address,
            'country' => $request->country,
            'city' => $request->city,
            'state' => $request->state,
            'zip' => $request->zip
        ];
        if($request->hasFile('image')){
            try{
                $removefile = $user->image ?: null;
                $user->image = store_file($request->image, file_path()['profile']['user']['path'], file_path()['profile']['user']['size'], $removefile);
            }catch (\Exception $exp){
                return back()->with('error', translate("Image could not be uploaded."));
            }
        }
        $user->address = $address;
        $user->save();
        return back()->with('success', translate("Profile has been updated"));
    }

    public function passwordUpdate(Request $request)
    {
        $this->validate($request, [
            'current_password' => 'nullable',
            'password' => 'required|confirmed',
        ]);
        $user = auth()->user();
        if($user->password){
            if(!Hash::check($request->current_password, $user->password)) {
                return back()->with('error', translate("The password does not match!"));
            }
            $user->password = Hash::make($request->password);
            $user->save();
        }else{
            $user->password = Hash::make($request->password);
            $user->save();
        }
        return back()->with('success', translate("Password has been updated"));
    }


    public function productReview(Request $request)
    {


        $request->validate([
            'product_id' => 'required',
            'rate'       => 'required',
            'review'     => 'required|max:240',

        ]);
        $user = Auth::user();
        
        $productRating             = new ProductRating();
        $productRating->user_id    = $user->id;
        $productRating->product_id = $request->product_id;
        $productRating->rating     = $request->rate;
        $productRating->review     = $request->review;
        $productRating->save();
        
        return back()->with('success', translate("Thanks for your review. We appreciate your feedback"));
        
    }

    // user subscribe
    public function subscribe(Request $request)
    {
        $request->validate([
            'submit' => 'in:subscribe,unsubscribe'
        ]);

        $subscribe = Subscriber::where('email', auth()->user()->email)->first();

        if($request->submit == 'subscribe'){
            $email = Auth::user()->email;
            if($email){
                $subscribe = new Subscriber();
                $subscribe->email = $email;
                $subscribe->save();
                return back()->with('success', translate("Successfully Subscribed"));
            }
        } else if($request->submit == 'unsubscribe') {
            $subscribe->delete();
            return back()->with('success', translate("UnSubscribed Successfully"));

        } else{
            return back()->with('error', translate("Something went Wrong"));
        }
    }

    public function refund(Request $request){
       $request->validate([
          "reason" => "required"
       ]);
       $flag = 1;
       $refund =  Refund::where('order_id',$request->order_id)->first();

        if($refund){
            if($refund->refund_status == 'success'){
                $flag = 0;
                return back()->with('error', translate("You Already Have A Refund Request which is Succeed !"));
            }
            elseif($refund->refund_status == 'pending'){
                $flag = 0;
                return back()->with('error', translate("You Already Have A Refund Request which is Under In Review !"));
            }
        }
        if($flag == 1){
            Refund::create([
                "user_id" => $request->user_id,
                "paymentID" => $request->payment_id,
                "trxID" => $request->order_id,
                "order_id" => $request->order_id,
                "method_id" => $request->method_id,
                "amount" => $request->amount,
                "reason" => $request->reason,
                "refund_status" => 'pending',
          ]);
          return back()->with('success', translate("Your Refund Request is Under Review Please Wait!!!"));
        }
    }



    public function deleteOrder(Request $request){

        $request->validate([
             'id'=>"required"
        ]);
        $order = Order::where('id',$request->id)->first();
        if($order){
            OrderDetails::where('order_id',$order->id)->delete();
            $order->delete();
            return back()->with('success', translate("Order Deleted"));
        }

        return back()->with('error', translate("Invalid Order"));

    }

    public function pay($id){
        $order = Order::where('id',$id)->first();

        $log =  PaymentLog::where('order_id',$order->id)->first();

        if($order && $log){
            session()->put('payment_track',$log->trx_number);
            return (new PaymentController())->paymentConfirm();
        }
        
        return back()->with('error', translate("Invalid Order"));
    }
}
