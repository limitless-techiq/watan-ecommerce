<?php

namespace App\Http\Controllers\Seller\Auth;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Seller;
use App\Models\SellerShopSetting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
class RegiterController extends Controller
{
    public function register()
    {
    
        $title = "Register as Seller";
        return view('seller.auth.register', compact('title'));
    }

    public function store(Request $request)
    {
        $request->validate([
        	'username' => 'required|max:255|unique:sellers,username',
        	'email' => 'required|email|max:255|unique:sellers,email',
        	'password' => 'required|confirmed|min:6',
        ]);


        $general = GeneralSetting::first();

        $rules =  [
            'username' => 'required|max:255|unique:sellers,username',
        	'email' => 'required|email|max:255|unique:sellers,email',
        	'password' => 'required|confirmed|min:6',
        ];


        if($general->strong_password == 1){
            $rules['password']    =  ["required","confirmed",Password::min(8)
                                            ->mixedCase()
                                            ->letters()
                                            ->numbers()
                                            ->symbols()
                                            ->uncompromised()
                                     ];
        }

        $request->validate($rules);

        $seller = Seller::create([
            'username' => $request->username,
            'email' => $request->email,
            'status' => '1',
            'password' => Hash::make($request->password),
        ]);
        SellerShopSetting::create([
        	'seller_id' => $seller->id,
        ]);
        Auth::guard('seller')->login($seller);
        return redirect(route('seller.dashboard'));
    }
}
