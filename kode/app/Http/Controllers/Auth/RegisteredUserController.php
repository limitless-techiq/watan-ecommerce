<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Http\Services\Frontend\ProductService;
use App\Models\GeneralSetting;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class RegisteredUserController extends Controller
{


    public $productService;
    public function __construct()
    {
        $this->productService = new ProductService();
    }

    

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {

        $generalSetting   = general_setting();

        $otpConfiguration = $generalSetting->otp_configuration 
                                ?  @json_decode($generalSetting->otp_configuration)
                                : null;

        $rules =  [
 
            'email'     => [    'nullable',
            Rule::requiredIf(function () use ( $otpConfiguration ) {
                $phoneOTP = @$otpConfiguration->phone_otp ?? 0;
                $emailOTP = @$otpConfiguration->email_otp ?? 0;
                return $emailOTP ==  1 || ($phoneOTP == 0 && $emailOTP == 0 );
            }) ,'email','unique:users'],

            'phone'     => ['nullable',Rule::requiredIf(function () use ( $otpConfiguration ) {
                return @$otpConfiguration->phone_otp ==  1 ;
            }) ,'unique:users,phone'],
            'password'            => ['required',Password::min(6),"confirmed"]
        ];


        if($generalSetting->strong_password == 1){
            $rules['password']    =  ["required","confirmed",Password::min(8)
                                            ->mixedCase()
                                            ->letters()
                                            ->numbers()
                                            ->symbols()
                                            ->uncompromised()
                                     ];
        }

        $request->validate($rules);

        $user = User::create([
            'name'     => $request->name,
            'email'    => @$request->email,
            'phone'    => @$request->phone,
            'password' => Hash::make($request->password),
        ]);


        if(@$otpConfiguration->phone_otp == 1 || @$otpConfiguration->email_otp == 1){
            return (new AuthenticatedSessionController())->sendOTP( $user , @$otpConfiguration);
        }


        Auth::login($user);
        $this->productService->updateCart(auth_user('web'));
        return redirect(RouteServiceProvider::HOME);
    }
}
