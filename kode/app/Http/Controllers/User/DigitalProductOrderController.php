<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\DigitalProductAttribute;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\PaymentMethod;
use App\Http\Utility\PaymentInsert;
use App\Http\Utility\SendMail;
use Illuminate\Support\Facades\Auth;

class DigitalProductOrderController extends Controller
{
    
    public function store(Request $request)
    {


        $rules = [
            'digital_attribute_id' => 'required',
            'digital_product_id'   => 'required|exists:products,id',
            'payment_type'         => 'required',
        ];

        if(!auth()->user()){
            $rules['email'] = 'required|email';
        }
        $request->validate($rules);
        $user = auth()->user();
        $product = Product::digital()->whereIn('status', ['1','0'])->where('id', $request->digital_product_id)->firstOrFail();

        $digitalProductAttribute = DigitalProductAttribute::where('product_id', $product->id)->where('id', $request->digital_attribute_id)->where('status', '1')->firstOrFail();

        if($request->payment_type != '1'){
            $paymentMethod = PaymentMethod::where('unique_code', $request->payment_type)->where('status', 1)->first();
            if(!$paymentMethod){
                return back()->wiht('error',translate('Invalid Payment gateway'));
            }
        }

        $order = Order::create([
            'customer_id' => $user?->id,
            'order_id' => general_setting()->order_prefix.random_number(),
            'amount' => $digitalProductAttribute->price,
            'payment_type' => $request->payment_type == 1 ? 1 : 2,
            'payment_status' => Order::UNPAID,
            'status' => Order::PLACED,
            'order_type' => Order::DIGITAL,
            'billing_information' => $request->email ? ['email' => $request->email,'username' => $request->email]: null,
        ]);
        OrderDetails::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'digital_product_attribute_id' => $digitalProductAttribute->id,
            'quantity' => 1,
            'total_price' => $digitalProductAttribute->price,
        ]);
        $mailCode = [
            'order_number' => $order->order_id,
        ];
        SendMail::MailNotification($user ?? $order->billing_information,'DIGITAL_PRODUCT_ORDER',$mailCode);
        if($request->payment_type == 1){
            return redirect()->route('user.dashboard')->with('success',translate("Your order has been submitted"));
        }else{
            session()->put('order_id', $order->order_id);
            PaymentInsert::paymentCreate($request->payment_type);
            return redirect()->route('user.payment.confirm');
        }
    }


    public function digitalOrderCancel(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:orders,id'
        ]);
        $user = Auth::user();
        $order = Order::where('id', $request->id)->where('customer_id', $user->id)->firstOrFail();
        if($order->payment_status == 1){
            $order->status = Order::CANCEL;
            $order->save();
            $orderDetail = OrderDetails::where('order_id', $order->id)->first();
            $orderDetail->status = Order::CANCEL;
            $orderDetail->save();
            return back()->with('success',translate('Digital Order has been canceled'));
        }else{
            return back()->with('error',translate('Invalid digital Order'));
        }
    }
}
