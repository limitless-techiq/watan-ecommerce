<?php

namespace App\Http\Controllers\PaymentMethod;

use App\Http\Controllers\Controller;

use App\Models\PaymentMethod;
use App\Models\PaymentLog;
use App\Models\GeneralSetting;

use App\Http\Utility\PaymentInsert;
use App\Models\Order;
use Illuminate\Http\Request;

class PaymentWithInstamojo extends Controller
{

    public $paymentMethod;
    public function __construct(){
        $this->paymentMethod = PaymentMethod::with(['currency'])->where('unique_code', 'INSTA106')->first();

    }


    public  function payment()
    {

       
        $basic = GeneralSetting::first();

        $paymentTrackNumber = session()->get('payment_track');
        $paymentLog = PaymentLog::with(['user'])->where('status', 0)->where('trx_number', $paymentTrackNumber)->first();


        if(!$paymentLog ||   !$this->paymentMethod){
            return redirect()->route('home')->with('error', translate('Invalaid Transaction'));
        }


        $siteName   = $basic->site_name;

        $api_key    = $this->paymentMethod->payment_parameter->api_key;
        $auth_token = $this->paymentMethod->payment_parameter->auth_token;
        $url        = 'https://instamojo.com/api/1.1/payment-requests/';
        $headers = [
            "X-Api-Key:$api_key",
            "X-Auth-Token:$auth_token"
        ];

  
        $postParam = [
            'purpose'                 => 'Payment to ' . $siteName,
            'amount'                  => round($paymentLog->final_amount,2),
            'buyer_name'              => optional($paymentLog->user)->name ?? 'User Name',
            'redirect_url'            => route('payment.success',['trx_number' => $paymentLog->trx_number]),
            'webhook'                 => route('instamojo.callback', [$paymentLog->trx_number]),
            'email'                   => optional($paymentLog->user)->email ?? 'example@example.com',
            'send_email'              => true,
            'allow_repeated_payments' => false
        ];




        $response = $this->curlPostRequestWithHeaders($url, $headers, $postParam);
        $response = json_decode($response);

    
        if ($response->success) {
            return redirect()->away($response->payment_request[0]->longurl);
        } 
        return redirect()->back()->with('error',  @$response->message??translate('Invalaid Transaction'));



    }

    public function callBack(Request $request ,$trx_code ,$type = null)
    {

        $paymentLog = PaymentLog::where('status', 0)->where('trx_number', $trx_code)->first();
        if(!$paymentLog){
            abort(404);
        }

        $salt     = trim($this->paymentMethod->payment_parameter->salt);
        $imData   = $_POST;
        $macSent  = $imData['mac'];
        unset($imData['mac']);
        ksort($imData, SORT_STRING | SORT_FLAG_CASE);
        $mac = hash_hmac("sha1", implode("|", $imData), $salt);

        if ($macSent == $mac && $imData['status'] == "Credit") {

            PaymentInsert::paymentUpdate($paymentLog->trx_number);
            Order::where('id',$paymentLog->order_id)->update([
                'payment_info'=>  json_encode($request->all())
            ]);
            return $this->paymentResponse($request,$paymentLog->trx_number ,true );
        }

        return $this->paymentResponse($request,$paymentLog->trx_number);
    }
}
