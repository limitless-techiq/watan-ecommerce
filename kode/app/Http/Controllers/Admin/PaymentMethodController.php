<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\Currency;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentMethodController extends Controller
{
    public function __construct(){
        $this->middleware(['permissions:view_method'])->only('index');
        $this->middleware(['permissions:update_method'])->only('edit',"update");
    }
    
    public function index() :View
    {
        $title          = "Payment methods";
        $paymentMethods = PaymentMethod::search()->latest()->with('currency')->get();
        return view('admin.payment.index', compact('title', 'paymentMethods'));
    }

    public function edit(string $slug,int $id) :View
    {
        $title         = "Payment method update";
        $paymentMethod = PaymentMethod::findOrFail($id);
        $currencies    = Currency::latest()->get();
        return view('admin.payment.edit', compact('title', 'paymentMethod', 'currencies'));
    }

    public function update(Request $request,int  $id) :RedirectResponse
    {
        $this->validate($request, [
            'status' => 'required|in:1,2',
            'image'  => 'nullable|image|mimes:jpg,png,jpeg',
        ]);
        $paymentMethod                 = PaymentMethod::findOrFail($id);
        $paymentMethod->currency_id    = $request->currency_id;
        $paymentMethod->percent_charge = $request->percent_charge;
        $paymentMethod->rate           = $request->rate;
        $paymentMethod->status         = $request->status;
        $parameter = [];
        foreach ($paymentMethod->payment_parameter as $key => $value) {
            $parameter[$key] = $request->method[$key];
        }
        $paymentMethod->payment_parameter = $parameter;
        if($request->hasFile('image')){
            try {
                $paymentMethod->image = store_file($request->image, file_path()['payment_method']['path'], null, $paymentMethod->image ?: null);
            }catch (\Exception $exp) {
    
            }
        }
        $paymentMethod->save();
        return back()->with('success',translate('Payment method has been updated'));
    }
    
   
}
