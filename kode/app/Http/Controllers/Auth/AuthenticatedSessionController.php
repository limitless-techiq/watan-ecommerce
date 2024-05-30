<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Services\Frontend\ProductService;
use App\Http\Utility\SendMail;
use App\Http\Utility\SendSMS;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
class AuthenticatedSessionController extends Controller
{

    public $productService;
    public function __construct()
    {
        $this->productService = new ProductService();
    }


   /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function login()
    {
        $title = "Login";
        return view('auth.login', compact('title'));
    }

   
    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {

        $generalSetting   = general_setting();

        $otpConfiguration = $generalSetting->otp_configuration 
                                ? @json_decode($generalSetting->otp_configuration)
                                : null;

        $field = preg_match('/^[0-9]+$/', request()->input('email')) ? 'phone' : 'email';

        if((@$otpConfiguration->email_otp == 1 &&  $field != 'email') && @$otpConfiguration->phone_otp == 0){
            return back()->with('error',translate('Please enter your email'));
        }
        if((@$otpConfiguration->phone_otp == 1 &&  $field != 'phone') && @$otpConfiguration->email_otp == 0){
            return back()->with('error',translate('Please enter your phone'));
        }

        $credentials  = [$field => request()->input('email') ,'password' => request()->input('password') ];

        if(@$otpConfiguration->login_with_password == 1){
             if(!Auth::attempt($credentials,true)){
                return back()->with('error',translate('Invalid credential'));
             }
            $user =  Auth::guard('web')->user();
        }else{
            $user = User::where(function ($query) use ($request) {
                $query->where('email', $request->input('email'))
                      ->orWhere('phone', $request->input('email'));
            })->first();  
        }

        if(@$otpConfiguration->phone_otp == 1 || @$otpConfiguration->email_otp == 1){
            Auth::guard('web')->logout();
            return $this->sendOTP( $user , @$otpConfiguration);
        }

        if(!$user){
            return back()->with('error',translate('Invalid credential'));
        }
        Auth::guard('web')->login($user);
        $this->productService->updateCart($user);


        if(session()->has('request_route')){
            $getRoutename  = session()->get('request_route');
            session()->forget('request_route');
            return redirect()->route($getRoutename);
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }


    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function otpVerificationView()
    {
        $title = "OTP Verification";
        return view('auth.otp_verification', compact('title'));
    }


    public function verifyOTP(Request $request){

        $request->validate([
            'otp' => 'required',
        ],[
            'otp.required' => 'The OTP filed is required'
        ]);

    
        $user = User::where('otp_code',$request->input('otp'))->first();

        if(!$user){
            return redirect()->back()->with('error','Invalid OTP code');
        }
        Auth::guard('web')->login($user);

        $this->productService->updateCart($user);
        return redirect()->intended(RouteServiceProvider::HOME);

    }
  
  
  



    public function sendOTP(User $user ,mixed $otpConfiguration){

        $user->otp_code =  random_number();
        $user->save();
        $templateCode = [
            'otp_code' => $user->otp_code, 
            'time'     => Carbon::now(),
        ];

        if(@$otpConfiguration->phone_otp == 1){
            if($user->phone){
                SendSMS::SMSNotification($user,'otp_verification',$templateCode);
            }
        }
        if(@$otpConfiguration->email_otp == 1){
            SendMail::MailNotification($user,'otp_verification',$templateCode);
        }

        return redirect()->route('otp.verification.view')->with('success',translate('Verification OTP  sent successfully to user, please check your  email or phone'));
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy()
    {
        Auth::guard('web')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    }
}
