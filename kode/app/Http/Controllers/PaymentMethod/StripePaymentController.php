<?php

namespace App\Http\Controllers\PaymentMethod;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\PaymentMethod;
use App\Models\PaymentLog;
use App\Http\Utility\PaymentInsert;
use App\Models\GeneralSetting;
use App\Models\Order;
use Session;

use StripeJS\Charge;
use StripeJS\Customer;
use StripeJS\StripeJS;
require_once('stripe-php/init.php');


class StripePaymentController extends Controller
{
  
    public $paymentMethod;
    public function __construct(){

        $this->paymentMethod = PaymentMethod::with(['currency'])->where('unique_code', 'STRIPE101')->first();
    }


    public function payment()
    {
        $paymentTrackNumber = session()->get('payment_track');
        $paymentLog         = PaymentLog::with(['user'])->where('status', 0)->where('trx_number', $paymentTrackNumber)->first();

        if(!$paymentLog || !$this->paymentMethod){
            return redirect()->route('home')->with('error', translate('Invalaid Transaction'));
        }


        $basic = GeneralSetting::first();

        $siteName           = $basic->site_name;;
        $val['key']         = $this->paymentMethod->payment_parameter->publishable_key;
        $val['name']        = optional($paymentLog->user)->name ?? $siteName;
        $val['description'] = "Payment with Stripe";
        $val['amount']      = round($paymentLog->final_amount,2) * 100;
        $val['currency']    =  $this->paymentMethod->currency->name;
        $send['val']        = $val;
        $send['src']        = "https://checkout.stripe.com/checkout.js";
        $send['view']       = 'user.payment.stripe';
        $send['method']     = 'post';
        $send['url']        = route('stripe.callback',['trx_code' => $paymentTrackNumber]);

        return view('user.payment.stripe',[
            'title' => translate('Payment with Stripe'),
            'paymentMethod' =>  $this->paymentMethod,
            'paymentLog' => $paymentLog,
            'data'       => (object) $send
        ]);

    }


    public function callBack(Request $request ,$trx_code ,$type = null)
    {

        $paymentLog = PaymentLog::with(['paymentGateway'])->where('status', 0)->where('trx_number', $trx_code)->first();

        if(!$paymentLog || !$this->paymentMethod){
            abort(404);
        }
    
        StripeJS::setApiKey($this->paymentMethod->payment_parameter->secret_key);

        $customer = Customer::create([
            'email'  => $request->stripeEmail,
            'source' => $request->stripeToken,
        ]);

        $charge = Charge::create([
            'customer'     => $customer->id,
            'description'  => 'Payment with Stripe',
            'amount'       => round($paymentLog->final_amount) * 100,
            'currency'     => $this->paymentMethod->currency->name
        ]);


        if ($charge['status'] == 'succeeded') {

            PaymentInsert::paymentUpdate($paymentLog->trx_number);
            Order::where('id',$paymentLog->order_id)->update([
                'payment_info'=>  json_encode($request->all())
            ]);

            return $this->paymentResponse($request,$paymentLog->trx_number ,true );

        }

        return $this->paymentResponse($request,$paymentLog->trx_number);


    }

}
