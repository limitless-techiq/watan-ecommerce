<?php

namespace App\Http\Controllers\Seller\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SellerAuthenticateController extends Controller
{
    public function __construct(){
        $this->middleware('seller.guest')->except('logout');
    }

    public function showLogin()
    {
        $title = "Seller Login";
        return view('seller.auth.login', compact('title'));
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);
        if (Auth::guard('seller')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('seller.dashboard');
        }
        return back()->with('error', translate("The provided credentials do not match our records"));
    }

    public function logout(Request $request)
    {
        Auth::guard('seller')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/seller');
    }
}
