<?php

namespace App\Http\Controllers\User;

use App\Enums\StatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Services\Frontend\CheckoutService;
use App\Http\Services\Frontend\ProductService;
use Illuminate\Http\Request;

use App\Models\ShippingDelivery;
use App\Models\PaymentMethod;
use App\Http\Utility\PaymentInsert;
use App\Http\Utility\SendMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{

    public $checkoutService,$productService;
    public function __construct()
    {
        $this->checkoutService = new CheckoutService();
        $this->productService = new ProductService();
    }

    public function checkout(? int $productId  =  null)
    {
        

        $title = "Checkout";
        $user  =  auth_user('web');
        $items = $this->productService->getCartItem();
        if($items->count() == 0){
            return back()->with("error",translate('No product added to your cart'));
        }
        $paymentMethods    = PaymentMethod::where('status', 1)->get();
        $shippingDeliverys = ShippingDelivery::where('status', 1)->orderBy('id', 'DESC')->with('method')->get();
        $countries         = json_decode(file_get_contents(resource_path(config('constants.options.country_code')) . 'countries.json'),true);

        return view('frontend.checkout', compact('title', 'items', 'shippingDeliverys', 'paymentMethods', 'user','countries'));
    }

    public function order(Request $request)
    {


        $rules = [
            'address_key' => 'required',
            'shipping_method' => 'required|exists:shipping_deliveries,id',
            'payment_type' => 'required',
        ];

        if (!auth()->user()) {
            unset($rules['address_key']);
            $rules += [
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

        $request->validate($rules,['address_key.required' => translate('Please Add Billing Address First')]);
    

        if(!auth()->user() && $request->input('create_account') == 1){
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
   
            Auth::guard('web')->login($user);
            $this->productService->updateCart(auth_user('web'));
        }

        

        if($request->payment_type != 1){
            $paymentMethod = PaymentMethod::where('unique_code', $request->payment_type)->where('status', 1)->first();
            if(!$paymentMethod){
                return back()->with("error",translate("Invalid Payment gateway"));
            }
        }

        $items = $this->productService->getCartItem();
       
        $calculations = $this->checkoutService->calculate($items);


        $shippingResponse = $this->checkoutService->shippingData($request);
        $order = $this->checkoutService->createOrder($request,$calculations,$shippingResponse);
        $this->checkoutService->createOrderDetails( $items,$order);
        $this->checkoutService->notifyUser($order);

        if($request->payment_type == 1){
            $this->checkoutService->cleanCart($items);
            return redirect()->route('order.success',$order->order_id)->with('success',translate("Your order has been submitted"));

        }else{
        
            session()->put('order_id', $order->order_id);
            PaymentInsert::paymentCreate($request->payment_type);
            return redirect()->route('user.payment.confirm');
        }
    }



}
