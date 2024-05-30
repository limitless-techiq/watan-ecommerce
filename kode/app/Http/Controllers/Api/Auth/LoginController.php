<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Utility\SendMail;
use App\Http\Utility\SendSMS;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class LoginController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        try {

            $generalSetting   = general_setting();

            $otpConfiguration = $generalSetting->otp_configuration 
                                    ? @json_decode($generalSetting->otp_configuration)
                                    : null;


            $field = preg_match('/^[0-9]+$/', request()->input('email')) ? 'phone' : 'email';
          
    
            $credentials  = [$field => request()->input('email') ,'password' => request()->input('password') ];

            $validator = Validator::make($request->all(),[
                'email'       => 'required',
                'password'    => [Rule::requiredIf(function () use ( $otpConfiguration , $request ) {
                                    return $request->input('login_type') == 'manual' &&  @$otpConfiguration->login_with_password == 1  ; 
                                }) ],
                'o_auth_id'   => 'required_if:login_type,o_auth',
                'login_type'  => ['required', Rule::in(['o_auth', 'manual'])],
            ]);

            if ($validator->fails()){
                return api(['errors'=>$validator->errors()->all()])->fails('Validation Error');
            }

            if ($request->input('login_type') === 'manual') {

                if(@$otpConfiguration->login_with_password == 1){

                    if(!Auth::attempt($credentials,true)){
                        return api(['errors' => ['Credentail Mismatch !!']])->fails(__('response.fail'));
                    }
                    $user =  Auth::guard('api')->user();

                }else{
                    $user = User::where(function ($query) use ($request) {
                        $query->where('email', $request->input('email'))
                              ->orWhere('phone', $request->input('email'));
                    })->first();   

                    if(!$user){
                        return api(['errors' => ['Credentail Mismatch !!']])->fails(__('response.fail'));
                    }
                }
        
                if(@$otpConfiguration->phone_otp == 1 || @$otpConfiguration->email_otp == 1){
                    return $this->sendOTP( $user , @$otpConfiguration);
                }

                $accessToken = $user->createToken('authToken')->plainTextToken;
                return api(['access_token' => $accessToken])->success(__('response.login.success'));
            }
            elseif($request->input('login_type') === 'o_auth'){

                $user = User::where('email',$request->email)->first();
                if($user){
                    $accessToken = $user->createToken('authToken')->plainTextToken;
                    return api(['access_token' => $accessToken])->success(__('response.login.success'));
                }
              
                $validator = Validator::make($request->all(),[
                    'email' => 'required|email|unique:users,email',
                ]);
        
                if ($validator->fails()){
                    return api(['errors'=>$validator->errors()->all()])->fails('Validation Error');
                }
                
                $user = new User();
                $user->email = $request->email;
                $user->google_id = $request->o_auth_id;
                $user->save();
                $accessToken = $user->createToken('authToken')->plainTextToken;
                return api(['access_token' => $accessToken])->success(__('response.login.success'));
                
            }
    

           return api(['errors' => ['Credentail Mismatch !!']])->fails(__('response.fail'));
                
        }catch (\Exception $exception){
            return api(['errors' => ['Credentail Mismatch !!']])->fails(__('response.fail'));

        }

        

    }

    public function sendOTP(User $user ,mixed $otpConfiguration){

        $user->otp_code =  random_number();
        $user->save();
        $templateCode = [
            'otp_code' => $user->otp_code, 
            'time' => Carbon::now(),
        ];

        $message = translate('Verification OTP  sent successfully to user, please check your  email or phone');

        if(@$otpConfiguration->phone_otp == 1){
            if($user->phone){
                 SendSMS::SMSNotification($user,'otp_verification',$templateCode);
            }
        }
        if(@$otpConfiguration->email_otp == 1){
   
                SendMail::MailNotification($user,'otp_verification',$templateCode);
            
        }

        return api(['message' => translate('Verification OTP  sent successfully to user, please check your  email or phone')])->success(__('response.success'));
    }

    public function logout (Request $request) : JsonResponse {

        $token = auth('api')->user()->tokens()->delete();
        return api(['message' => translate('You have been successfully logged out!')])->success(__('response.success'));

    }



    public function verifyOTP(Request $request){

        $validator = Validator::make($request->all(),[
            'otp'       => 'required',
        ]);

    
        if ($validator->fails()){
            return api(['errors'=>$validator->errors()->all()])->fails('Validation Error');
        }
        $user = User::where('otp_code',$request->input('otp'))->first();

        if(!$user){
            return api(['errors' => ['Invalid OTP code']])->fails(__('response.fail'));
        }

        $accessToken = $user->createToken('authToken')->plainTextToken;
        return api(['access_token' => $accessToken])->success(__('response.login.success'));


    }

}
