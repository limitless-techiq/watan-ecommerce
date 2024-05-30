<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Enums\StatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.guest')->except('logout');
    }

    public function showLogin()
    {
        $title = "Admin Login";
        return view('admin.auth.login', compact('title'));
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'user_name' => ['required'],
            'password' => ['required'],
        ]);

        $remember_me = $request->has('remember_me') ? true : false; 

        if (Auth::guard('admin')->attempt($credentials, $remember_me )) {
            if(auth_user()->status  == StatusEnum::false->status()){
                Auth::guard('admin')->logout();
                return redirect()->route('admin.login')->with("error",'Your account is banned by superadmin');
            }
            $request->session()->regenerate();
            return redirect()->route('admin.dashboard');
        }
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/admin');
    }
}
