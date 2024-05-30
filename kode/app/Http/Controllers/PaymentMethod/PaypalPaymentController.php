<?php

namespace App\Http\Controllers\PaymentMethod;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\PaymentMethod;
use App\Models\PaymentLog;


use Illuminate\Support\Facades\Redirect;

use App\Http\Utility\PaymentInsert;
use App\Models\GeneralSetting;
use App\Models\Order;

class PaypalPaymentController extends Controller
{


    public $paymentMethod;
    public function __construct(){
        $this->paymentMethod = PaymentMethod::with(['currency'])->where('unique_code', 'PAYPAL102')->first();

    }


    public  function payment()
    {
        $basic = GeneralSetting::first();
        $siteName   = $basic->site_name;

        $paymentTrackNumber = session()->get('payment_track');
        $paymentLog = PaymentLog::with(['user'])->where('status', 0)->where('trx_number', $paymentTrackNumber)->first();

        if(!$paymentLog ||   !$this->paymentMethod){
            return redirect()->route('home')->with('error', translate('Invalaid Transaction'));
        }

        $param['cleint_id']   = $this->paymentMethod->payment_parameter->client_id;
        $param['description'] = "Payment To {$siteName} Account";
        $param['custom_id']   = $paymentLog->trx_number;
        $param['amount']      = round($paymentLog->final_amount,2);
        $param['currency']    = $this->paymentMethod->currency->name;

        return view('user.payment.paypal',[
            'title' => translate('Payment with Paypal'),
            'paymentMethod' =>  $this->paymentMethod,
            'paymentLog' => $paymentLog,
            'data'       => (object) $param
        ]);

    }


    public function callBack(Request $request ,$trx_code , mixed $type = null)
    {

        $paymentLog = PaymentLog::where('status', 0)->where('trx_number', $trx_code)->first();

        if(!$paymentLog){
           abort(404);
        }

        $url         = "https://api.paypal.com/v2/checkout/orders/{$type}";
        $client_id   = $this->paymentMethod->payment_parameter->client_id;
        $secret      = $this->paymentMethod->payment_parameter->secret;
        $headers = [
            'Content-Type:application/json',
            'Authorization:Basic ' . base64_encode("{$client_id}:{$secret}")
        ];
        $response     = $this->curlGetRequestWithHeaders($url, $headers);
        $paymentData  = json_decode($response, true);

        
        if (isset($paymentData['status']) && $paymentData['status'] == 'COMPLETED') {

            if ($paymentData['purchase_units'][0]['amount']['currency_code'] == $this->paymentMethod->currency->name) {
                PaymentInsert::paymentUpdate($paymentLog->trx_number);
                Order::where('id',$paymentLog->order_id)->update([
                    'payment_info'=>  json_encode($paymentData)
                ]);
                return $this->paymentResponse($request,$paymentLog->trx_number ,true );
            } 
        } 

        return $this->paymentResponse($request,$paymentLog->trx_number);
        
    }


}

