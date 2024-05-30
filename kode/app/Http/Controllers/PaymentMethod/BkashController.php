<?php

namespace App\Http\Controllers\PaymentMethod;

use App\Http\Controllers\Controller;
use App\Http\Utility\PaymentInsert;
use App\Models\Order;
use App\Models\PaymentLog;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Config;

use Karim007\LaravelBkashTokenize\Facade\BkashPaymentTokenize;


class BkashController extends Controller
{


    public $paymentMethod;
    public function __construct(){
        $this->paymentMethod = PaymentMethod::where('unique_code', 'BKASH102')->first();

    }
    

    public function config($trxCode)
    {
      
        if(!$this->paymentMethod){
            return false;
        }
        $sandbox = true;
        if ($this->paymentMethod->payment_parameter->environment == 'live') {
            $sandbox = false;
        } 
        
        $config = [
            'sandbox'=>   $sandbox ,
            'bkash_app_key'=> $this->paymentMethod->payment_parameter->api_key ,
            'bkash_app_secret'=>  $this->paymentMethod->payment_parameter->api_secret ,
            'bkash_username'=>  $this->paymentMethod->payment_parameter->user_name ,
            'bkash_password'=>  $this->paymentMethod->payment_parameter->password ,
            'callbackURL'=>   route('bkash.callback',$trxCode) ,
            "timezone" => "Asia/Dhaka"
        ];

        Config::set('bkash',  $config );

        return true;

    }


    public function payment()
    {

   
        $paymentTrackNumber = session()->get('payment_track');
        $paymentLog = PaymentLog::where('status', 0)->where('trx_number', $paymentTrackNumber)->first();
        if(!$paymentLog || !$this->config($paymentLog->trx_number)){
            return redirect()->route('home')->with('error', translate('Invalaid Transaction'));
        }
        $inv = uniqid();
        $request['intent'] = 'sale';
        $request['mode'] = '0011'; 
        $request['payerReference'] = $inv;
        $request['currency'] = 'BDT';
        $request['amount'] = round($paymentLog->final_amount,2);
        $request['merchantInvoiceNumber'] = $inv;
        $request['callbackURL'] = config("bkash.callbackURL");

        $request_data_json = json_encode($request);

        $response =  BkashPaymentTokenize::cPayment($request_data_json);
        
        if (isset($response['bkashURL'])) {return redirect()->away($response['bkashURL']);}
    
        return redirect()->route('home')->with('error', $response['statusMessage']);
        
      
    }

    public function callBack(Request $request ,$trx_code)
    {

        $paymentLog = PaymentLog::where('status', 0)->where('trx_number', $trx_code)->first();
        
        if(!$paymentLog || !$this->config($paymentLog->trx_number)){
           abort(404);
        }

        if ($request->status == 'success'){
            $response = BkashPaymentTokenize::executePayment($request->paymentID); 
        
            if (isset($response['statusCode']) && $response['statusCode'] == "0000" && $response['transactionStatus'] == "Completed") {
                
                PaymentInsert::paymentUpdate($paymentLog->trx_number);
                Order::where('id',$paymentLog->order_id)->update([
                    'payment_info'=>  json_encode(request()->all())
                ]);
                return $this->paymentResponse($request,$paymentLog->trx_number ,true );
            }
  
        }

        return $this->paymentResponse($request,$paymentLog->trx_number);
    }

    

   

}
