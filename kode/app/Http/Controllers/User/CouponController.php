<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Coupon;


class CouponController extends Controller
{
    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code'     => 'required',
            'subtotal' => 'required|numeric|gt:0',
        ],[
            'code.required' => 'Coupon code is required'
        ]);
 
        if(session()->has('coupon') && session('coupon')['code'] == $request->code){
            return response()->json(['error'=>'Coupon has applied already']);
        }
        $now = Carbon::now();
        $coupon = Coupon::where('code', $request->code)->where('start_date', '<=', $now)->where('end_date', '>=', $now)->where('status', 1)->first();
 
        if(!$coupon || $coupon->code == $request->couponCode){
            return response()->json(['error'=>'This coupon doesn\'t exist']);
        }
        $amount = round(($coupon->discount(($request->subtotal))));

        if((int)$amount == 0){
            return response()->json(['error'=>'Sorry, your order total doesn\'t meet the requirements for this coupon']);
        }


        $response = [
            'success' => 'Coupon has applied successfully',
            'code'   => $coupon->code,
            'amount' => $amount,
        ]; 
        session()->put('coupon', ['code'=>$coupon->code,'amount' => $amount]);
        return response()->json($response);
    }
}