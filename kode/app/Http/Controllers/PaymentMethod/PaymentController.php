<?php

namespace App\Http\Controllers\PaymentMethod;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\PaymentLog;
use App\Models\PaymentMethod;
use Razorpay\Api\Api;
class PaymentController extends Controller
{

    public function preview()
    {

    	$title = "Payment Info";
    	$paymentTrackNumber = session()->get('payment_track');
    	$paymentLog = PaymentLog::where('trx_number', $paymentTrackNumber)->first();
    	return view('user.payment', compact('title', 'paymentLog'));
    }

    public function paymentConfirm()
    {
    	$paymentTrackNumber = session()->get('payment_track');

    	$paymentLog = PaymentLog::with('paymentGateway')->where('trx_number', $paymentTrackNumber)->first();
        if(!$paymentLog){
            return back()->with("error",translate('Invalid order'));
        }

    	$paymentMethod = PaymentMethod::where('unique_code', $paymentLog->paymentGateway->unique_code)->first();

    	if(!$paymentMethod){
            return back()->with('error',translate("Invalid Payment gateway"));
        }
    	if($paymentLog->paymentGateway->unique_code == "STRIPE101"){
            return (new StripePaymentController())->payment();
    	}
        if($paymentLog->paymentGateway->unique_code == "BKASH102"){
    		$title = "Payment with Bkash";
    		return view('user.payment.bkash', compact('title','paymentLog','paymentMethod'));
    	}
        if($paymentLog->paymentGateway->unique_code == "NAGAD104"){
    		$title = "Payment with Nagad";
    		return view('user.payment.nagad', compact('title','paymentLog','paymentMethod'));
    	}
        else if($paymentLog->paymentGateway->unique_code == "PAYPAL102"){
            return (new PaypalPaymentController())->payment();
        }else if($paymentLog->paymentGateway->unique_code == "PAYSTACK103"){
            return (new PaystackPayment())->payment();
        }
        else if($paymentLog->paymentGateway->unique_code == "FLUTTERWAVE105"){
            return (new FlutterwavePaymentController())->payment();
        }
        else if($paymentLog->paymentGateway->unique_code == "INSTA106"){
            $title = "Payment with Instamojo";
            return view('user.payment.instamojo', compact('title', 'paymentMethod', 'paymentLog'));
        }
        else if($paymentLog->paymentGateway->unique_code == "RAZORPAY106"){
            return (new RazorpayPaymentController())->payment();
        }

        session()->forget('payment_track');
        return redirect()->route('home');
    
    }

    public function paymentSuccess($trx_number){
        
        $paymentLog = PaymentLog::with(['paymentGateway'])->where('status', 2)->where('trx_number', $trx_number)->firstOrfail();
        $order      = Order::where('id', $paymentLog->order_id)->firstOrfail();
        return  view("frontend.payment_success",[
            'title'      => translate('Payment Success'),
            'paymentLog' => $paymentLog,
            'order' => $order ,
        ]);
    }
    public function paymentFailed(){
        
        return  view("frontend.payment_failed",[
            'title' => translate('Payment Failed')
        ]);
    }
    
    public function orderSuccess($orderId){
        
        $order      = Order::where('order_id', $orderId)->firstOrfail();
        return  view("frontend.order_success",[
            'title'      => translate('Order Success'),
            'order' => $order ,
        ]);
    }
}
