<?php

namespace App\Http\Services\Frontend;
use App\Http\Controllers\Controller;
use App\Models\ShippingDelivery;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\ProductStock;
use App\Http\Utility\SendMail;
use Illuminate\Support\Arr;

class CheckoutService extends Controller
{

    /** calculate total amount discount & qty */
    public function calculate($items){
        $response = [];
        $couponAmount = 0; $totalCartAmount = 0; $totalQuantity = 0;
        foreach($items as $item){
            $totalCartAmount += $item->total;
            $totalQuantity += $item->quantity;
        }
        if(session()->has('coupon')){
            $coupon = Coupon::where('code', session()->get('coupon')['code'])->first();
            $couponAmount = round(($coupon->discount($totalCartAmount)));
        }
        $response['coupon_amount'] =    $couponAmount;
        $response['total_cart_amount'] =    $totalCartAmount;
        $response['total_quantity'] =    $totalQuantity;
        return $response;

    }

    /**get shipping info data */
    public function shippingData($request){

        $response['shipping_delivery'] = ShippingDelivery::find($request->shipping_method);
        $user = auth_user('web');
        if( $user ){
            $billingInfos = (array)@$user->billing_address;
            $billinginfo  = (array)@Arr::get($billingInfos ,$request->address_key ,null) ;
        }

        $response['address'] = $user ?  $billinginfo :  [
            'first_name' => $request->first_name,
            'username' => $request->username ?? $request->first_name ,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'zip' => $request->zip,
            'city' => $request->city,
            'state' => $request->state,
        ];
        
        return $response;

    }

    /**
     * create an order 
     */
    public function createOrder($request ,$cal,$shippingData){
        $totalAmount = $cal['total_cart_amount'] - $cal['coupon_amount'] + $shippingData['shipping_delivery']->price;
        $order = Order::create([
            'shipping_deliverie_id' => $shippingData['shipping_delivery']->id,
            'customer_id'           => auth()->user()?auth()->user()->id :null,
            'qty'       => $cal['total_quantity'],
            'order_id' => general_setting()->order_prefix.random_number(),
            'shipping_charge' => $shippingData['shipping_delivery']->price,
            'discount' => $cal['coupon_amount'],
            'amount' => $totalAmount,
            'payment_type' => $request->payment_type == 1 ? 1:2,
            'payment_status' => Order::UNPAID,
            'order_type' => Order::PHYSICAL,
            'status' => Order::PLACED,
            'billing_information' => $shippingData['address']
        ]);
        return $order;
    }

    /** order details */
    public function createOrderDetails($items,$order){
        
        foreach($items as $item){
            if($item->attributes_value){
                $productStock = ProductStock::where('product_id', $item->product_id)->where('attribute_value', $item->attributes_value)->first();
                if($productStock){
                    $productStock->qty -= $item->quantity;
                    if($productStock->qty < 0){
                        $productStock->qty  = 0;
                    }
                    $productStock->save();
                }
            }
          
            OrderDetails::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'attribute' => $item->attributes_value,
                'total_price' => $item->total
            ]);
        }

    }

    /** send email to  user */
    public function notifyUser($order){
        session()->put('order_id', $order->order_id);
        if(session()->has('coupon')){
           session()->forget('coupon');
        }
        $mailCode = [
            'order_number' => $order->order_id,
        ];

        $user = auth()->user() ? auth()->user() :$order->billing_information;
        SendMail::MailNotification( $user,'ORDER_PLACED',$mailCode);
    }

    /** clear user cart */
    public function cleanCart($items){
        foreach($items as $item){
            $item->delete();
        }
    }


}