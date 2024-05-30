<?php

namespace App\Http\Controllers\PaymentMethod;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Razorpay\Api\Api;
use App\Models\PaymentMethod;
use App\Models\PaymentLog;
use App\Http\Utility\PaymentInsert;
use App\Models\GeneralSetting;
use App\Models\Order;
require_once('razorpay-php/Razorpay.php');
class RazorpayPaymentController extends Controller
{
    public $paymentMethod;
    public function __construct(){
        $this->paymentMethod = PaymentMethod::with(['currency'])->where('unique_code', 'RAZORPAY106')->first();

    }


    public function payment()
    {

        try {
            $paymentTrackNumber = session()->get('payment_track');
            $paymentLog         = PaymentLog::with(['user'])->where('status', 0)->where('trx_number', $paymentTrackNumber)->first();
    
            if(!$paymentLog || !$this->paymentMethod){
                return redirect()->route('home')->with('error', translate('Invalaid Transaction'));
            }
    
            
            $api_key          = $this->paymentMethod->payment_parameter->razorpay_key ?? '';
            $api_secret       = $this->paymentMethod->payment_parameter->razorpay_secret ?? '';
            $razorPayApi      = new Api($api_key, $api_secret);
    
            $finalAmount      = round($paymentLog->final_amount * 100, 2);
            $gatewayCurrency  = $this->paymentMethod->currency->name;
    
            $trx =  $paymentTrackNumber ;
            $razorOrder = $razorPayApi->order->create(
                array(
                    'receipt'         => $trx,
                    'amount'          => $finalAmount,
                    'currency'        => $gatewayCurrency,
                    'payment_capture' => '0'
                )
            );
    
            $basic = GeneralSetting::first();
            $val['key']             = $api_key;
            $val['amount']          = $finalAmount;
            $val['currency']        = $gatewayCurrency;
            $val['order_id']        = $razorOrder['id'];
            $val['buttontext']      = "Payment via Razorpay";
            $val['name']            = optional($paymentLog->user)->username;
            $val['description']     = "Payment By Razorpay";
            $val['image']           = show_image('assets/images/backend/AdminLogoIcon/'.$basic->admin_logo_sm);
            $val['prefill.name']    = optional($paymentLog->user)->username;
            $val['prefill.email']   = optional($paymentLog->user)->email;
            $val['prefill.contact'] = optional($paymentLog->user)->phone;
            $val['theme.color']     = "#2ecc71";
            $send['val']            = $val;
    
            $send['method']       = 'POST';
            $send['url']          = route('razorpay.callback',[$trx]);
            $send['custom']       = $trx;
            $send['checkout_js']  = "https://checkout.razorpay.com/v1/checkout.js";
            $send['view']         = 'user.payment.razorpay';
    
    
            return view('user.payment.razorpay',[
                'title' => translate('Payment with Razorpay'),
                'paymentMethod' =>  $this->paymentMethod,
                'paymentLog' => $paymentLog,
                'data'       => (object) $send
            ]);
        } catch (\Exception $ex) {
           return back()->with("error",translate($ex->getMessage()));
        }
       

    }


    public function callBack(Request $request ,$trx_code ,$type = null)
    {

        $paymentLog = PaymentLog::where('status', 0)->where('trx_number', $trx_code)->first();

        if(!$paymentLog || !$this->paymentMethod){
            abort(404);
        }

        $api_secret          = $this->paymentMethod->payment_parameter->razorpay_secret;
        $signature           = hash_hmac('sha256', $request->razorpay_order_id . "|" . $request->razorpay_payment_id, $api_secret);

        if ($signature == $request->razorpay_signature) {
            PaymentInsert::paymentUpdate($paymentLog->trx_number);
            Order::where('id',$paymentLog->order_id)->update([
                'payment_info'=>  json_encode($request->all())
            ]);
            return $this->paymentResponse($request,$paymentLog->trx_number ,true );
        }
        return $this->paymentResponse($request,$paymentLog->trx_number);
    }
}
