<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentLog;
use Carbon\Carbon;
use Illuminate\View\View;

class PaymentLogController extends Controller

{

    public function __construct(){
        $this->middleware(['permissions:view_log']);
    }
    public function index() :View
    {
    	$title        = "Payments log";
    	$paymentLogs  = PaymentLog::latest()->search()->date()->where('status', '!=', 0)->with('user','paymentGateway','paymentGateway.currency')->paginate(paginate_number());
    	return view('admin.payment_log.index', compact('title', 'paymentLogs'));
    }

    public function pending() :View
    {
    	$title       = "Pending payments log";
    	$paymentLogs = PaymentLog::search()->date()->where('status', 1)->with('user','paymentGateway','paymentGateway.currency')->paginate(paginate_number());
    	return view('admin.payment_log.index', compact('title', 'paymentLogs'));
    }

    public function approved() :View
    {
    	$title       = "Approved payments log";
    	$paymentLogs = PaymentLog::search()->date()->where('status', 2)->with('user','paymentGateway','paymentGateway.currency')->paginate(paginate_number());
    	return view('admin.payment_log.index', compact('title', 'paymentLogs'));
    }

    public function rejected() :View
    {
    	$title = "Rejected payments log";
    	$paymentLogs= PaymentLog::search()->date()->where('status', 3)->with('user','paymentGateway','paymentGateway.currency')->paginate(paginate_number());
    	return view('admin.payment_log.index', compact('title', 'paymentLogs'));
    }

}
