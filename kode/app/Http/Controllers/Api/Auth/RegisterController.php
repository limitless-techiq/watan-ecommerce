<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    public function register(Request $request): \Illuminate\Http\JsonResponse
    {
        $generalSetting   = general_setting();

        $otpConfiguration = $generalSetting->otp_configuration 
                                ?  @json_decode($generalSetting->otp_configuration)
                                : null;

       
    
        $validator = Validator::make($request->all(),([
            'name'      => 'required|string',
            'email'     => ['nullable',Rule::requiredIf(function () use ( $otpConfiguration ) {
                $phoneOTP = @$otpConfiguration->phone_otp ?? 0;
                $emailOTP = @$otpConfiguration->email_otp ?? 0;
                return $emailOTP ==  1 || ($phoneOTP == 0 && $emailOTP == 0 );
            }) ,'email','unique:users'],

            'phone'     => ['nullable',Rule::requiredIf(function () use ( $otpConfiguration ) {
                return @$otpConfiguration->phone_otp ==  1 ;
            }) ,'unique:users,phone'],
            'password'  => 'required|confirmed',
        ]));

        if ($validator->fails()){
            return api(['errors'=>$validator->errors()->all()])->fails(__('response.fail'));
        }

        $user = new User([
            'name'     => $request->input('name'),
            'email'    => $request->input('email'),
            'phone'    => $request->input('phone'),
            'password' => bcrypt($request->input('password')),
        ]);

        if(@$otpConfiguration->phone_otp == 1 || @$otpConfiguration->email_otp == 1){
            return (new LoginController())->sendOTP( $user , @$otpConfiguration);
        }

        $user->save();
        $token = $user->createToken('authToken')->plainTextToken;

        return api([
            'access_token' => $token
        ])->success(translate('Registration Success'));
    }
}
