<?php
namespace App\Http\Utility;
use App\Models\PaymentMethod;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\PaymentLog;
use App\Models\User;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Seller;
use App\Models\Transaction;
use App\Models\DigitalProductAttribute;
use App\Models\GeneralSetting;
use App\Http\Utility\SendMail;

class PaymentInsert
{
    public static function paymentCreate($gatewayId,$orderId = null)
    {
        $paymentMethod = PaymentMethod::where('unique_code', $gatewayId)->where('status', 1)->first();
        if(!$paymentMethod){
            return back()->wit('error',translate("Invalid Payment gateway"));
        }
      

        $order_id =  !$orderId  ? session('order_id') : $orderId;
      
        $order = Order::where('order_id',$order_id)
                
                  ->where('payment_status', Order::UNPAID)->first();

        $charge = ($order->amount * $paymentMethod->percent_charge / 100);

        $final_amount             =  ($order->amount  + $charge )*$paymentMethod->rate;

        $paymentLog = PaymentLog::create([
            'order_id' => $order->id,
            'user_id' => auth()->user() ? auth()->user()->id : null,
            'method_id' => $paymentMethod->id,
            'charge' => $charge,
            'rate' => $paymentMethod->rate,
            'amount' => $order->amount ,
            'final_amount' => $final_amount,
            'trx_number' => trx_number(),
            'status' => 0,
        ]);
        if(session()->get('payment_track')){
            session()->forget('payment_track');
        }
        session()->put('payment_track', $paymentLog->trx_number);
        return $paymentLog;
    }

    public static function paymentUpdate($trx , $paymentId=null)
    {
      
        $general = GeneralSetting::first();
        $paymentData = PaymentLog::where('trx_number', $trx)->first();
        if($paymentData && $paymentData->status == 0){
          
            $paymentData->status = 2;
            $paymentData->save();
            $user = User::find($paymentData->user_id);
            if(@$user){
                Cart::where('user_id', $user->id)->delete();
            }
            else{
                Cart::whereNotNull('session_id')->where('session_id', session()->get('session_id'))->delete();
            }
    
            $order = Order::with('digitalProductOrder')->where('id', $paymentData->order_id)->first();
            
            $transaction = Transaction::create([
                'user_id'            => $user  ? $user->id : null,
                'amount'             => $paymentData->amount,
                'post_balance'       => 0,
                'transaction_type'   => Transaction::PLUS,
                'transaction_number' => trx_number(),
                'guest_user'         =>  !$user ?    $order->billing_information : null,
                'details'            => 'Payment Via ' . $paymentData->paymentGateway->name,
            ]);


            $order->payment_status= Order::PAID;
            if($order->payment_type == 2){
                $order->payment_method_id= $paymentData->method_id;
                if($paymentId !=null){
                    $order->payment_id = $paymentId;
                }
            }


            if($order->order_type == Order::DIGITAL){

                $digitalProductAttribute = DigitalProductAttribute::find(@$order->digitalProductOrder->digital_product_attribute_id);

                if($digitalProductAttribute){
                    $digitalProductAttribute->status = 0;
                    $digitalProductAttribute->save();
                }
                $order->status = Order::PROCESSING;
                $order->save();


                OrderDetails::where('order_id',$order->id)->update([
                    'status' => Order::PROCESSING
                ]);
        
                $product = Product::find($order->digitalProductOrder->product_id);
                if($product && $product->seller_id){
                    $commission  = (($order->amount * $general->commission)/100);
                    $finalAmount = $order->amount - $commission;

                    $seller = Seller::findOrFail($product->seller_id);
                    $seller->balance += $finalAmount;
                    $seller->save();

                    $transaction = Transaction::create([
                        'seller_id' => $seller->id,
                        'amount' => $finalAmount,
                        'post_balance' => $seller->balance,
                        'transaction_type' => Transaction::PLUS,
                        'transaction_number' => trx_number(),
                        'details' =>  $order->order_id.' order number amount added',
                    ]);
                }
            }
          
            $order->save();
    
            $mailCode = [
                'trx' => $paymentData->trx_number,
                'amount' => ($paymentData->final_amount),
                'charge' => ($paymentData->charge),
                'currency' => $general->currency_name,
                'rate' => ($paymentData->rate),
                'method_name' => $paymentData->paymentGateway->name,
                'method_currency' => $paymentData->paymentGateway->currency->name,
            ];
            
            SendMail::MailNotification($user?? $order->billing_information ,'PAYMENT_CONFIRMED',$mailCode);
        }
    }
}



