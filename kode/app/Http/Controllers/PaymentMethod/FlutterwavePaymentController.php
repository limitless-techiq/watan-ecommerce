<?php

namespace App\Http\Controllers\PaymentMethod;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentLog;
use App\Models\PaymentMethod;
use Carbon\Carbon;
use App\Models\User;
use App\Http\Utility\PaymentInsert;
use App\Models\Order;

class FlutterwavePaymentController extends Controller
{



    public $paymentMethod;
    public function __construct(){
        $this->paymentMethod = PaymentMethod::with(['currency'])->where('unique_code', 'FLUTTERWAVE105')->first();

    }

    public function payment()
    {

        $paymentTrackNumber = session()->get('payment_track');
        $paymentLog = PaymentLog::with(['user'])->where('status', 0)->where('trx_number', $paymentTrackNumber)->first();

        if(!$paymentLog || !$this->paymentMethod){
            return redirect()->route('home')->with('error', translate('Invalaid Transaction'));
        }


        $send['API_publicKey']    = $this->paymentMethod->payment_parameter->public_key;
        $send['customer_email']   = optional($paymentLog->user)->email;
        $send['amount']           = round($paymentLog->final_amount,2);
        $send['customer_phone']   = optional($paymentLog->user)->phone ?? '';
        $send['currency']         = $this->paymentMethod->currency->name;
        $send['txref']            = $paymentTrackNumber;

        return view('user.payment.flutterwave',[
            'title' => translate('Payment with Flutterwave'),
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

        if ($type != 'error') {

            $url         = 'https://api.ravepay.co/flwv3-pug/getpaidx/api/v2/verify';
            $headers     = ['Content-Type:application/json'];
            $postParam   = array(
                "SECKEY" =>$this->paymentMethod->payment_parameter->secret_key ?? '',
                "txref"  =>$paymentLog->trx_number
            );

            $gwResponse = $this->curlPostRequestWithHeaders($url, $headers, $postParam);

            $response   = json_decode($gwResponse);

            if ($response->data->status == "successful" && $response->data->chargecode == "00") {

                PaymentInsert::paymentUpdate($paymentLog->trx_number);
                Order::where('id',$paymentLog->order_id)->update([
                    'payment_info'=>  json_encode($gwResponse)
                ]);
                return $this->paymentResponse($request,$paymentLog->trx_number ,true );

            } 
        } 

        return $this->paymentResponse($request,$paymentLog->trx_number);

    }
}
