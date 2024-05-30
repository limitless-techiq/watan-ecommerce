<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Config;
class SocialLoginServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        try {
            $general = GeneralSetting::first();
         
            if($general){
                if($general->s_login_google_info){
                    $google_login = json_decode($general->s_login_google_info,true);
                    if($google_login['g_status'] == '1'){
                        $google = array(
                            'client_id' => $google_login['g_client_id'],
                            'client_secret' => $google_login['g_client_secret'],
                            'redirect' => url('auth/google/callback'),
                        );
                        Config::set('services.google', $google);
                    }
                }
                if($general->s_login_facebook_info){
                    $facebook_login = json_decode($general->s_login_facebook_info,true);
                    if($facebook_login['f_status'] == '1'){
                        $facebook = array(
                            'client_id' => $facebook_login['f_client_id'],
                            'client_secret' =>  $facebook_login['f_client_secret'],
                            'redirect' => url('auth/facebook/callback'),
                        );
                        Config::set('services.facebook', $facebook);
                    }
                }
            }
        }catch(\Exception $exception){

        }
    }
}
