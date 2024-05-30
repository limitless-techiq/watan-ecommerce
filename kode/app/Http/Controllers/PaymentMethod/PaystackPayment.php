<?php

namespace App\Http\Controllers\PaymentMethod;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Utility\PaymentInsert;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\PaymentLog;

class PaystackPayment extends Controller
{


    public $paymentMethod;
    public function __construct(){
        $this->paymentMethod = PaymentMethod::with(['currency'])->where('unique_code', 'PAYSTACK103')->first();

    }


    public function payment()
    {
        
        $paymentTrackNumber = session()->get('payment_track');
        $paymentLog = PaymentLog::with(['user'])->where('status', 0)->where('trx_number', $paymentTrackNumber)->first();

        if(!$paymentLog ||   !$this->paymentMethod){
            return redirect()->route('home')->with('error', translate('Invalaid Transaction'));
        }


        $send['key']      = $this->paymentMethod->payment_parameter->public_key;
        $send['email']    = optional($paymentLog->user)->email;
        $send['amount']   = round($paymentLog->final_amount * 100,2);
        $send['currency'] = $this->paymentMethod->currency->name;
        $send['ref']      = $paymentTrackNumber;

        return view('user.payment.paystack',[
            'title' => translate('Payment with Paystack'),
            'paymentMethod' =>  $this->paymentMethod,
            'paymentLog' => $paymentLog,
            'data'       => (object) $send
        ]);



	}


    public function callBack(Request $request ,$trx_code ,$type = null)
    {

        $paymentLog = PaymentLog::where('status', 0)->where('trx_number', $trx_code)->first();
        if(!$paymentLog || !$this->paymentMethod){
            abort(404);
        }

        $secret_key  = $this->paymentMethod->payment_parameter->secret_key;
        $url         = 'https://api.paystack.co/transaction/verify/' . $trx_code;
        $headers = [
            "Authorization: Bearer {$secret_key}"
        ];
        $response = $this->curlGetRequestWithHeaders($url, $headers);

        $response = json_decode($response, true);


        if ($response && isset($response['data'])) {

            if ($response['data']['status'] == 'success') {    
                PaymentInsert::paymentUpdate($paymentLog->trx_number);
                Order::where('id',$paymentLog->order_id)->update([
                    'payment_info'=>  json_encode($response)
                ]);
                return $this->paymentResponse($request,$paymentLog->trx_number ,true );
            }
        } 

        return $this->paymentResponse($request,$paymentLog->trx_number);


    }
}
