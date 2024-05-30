<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $onboarding_data =  [];
        if($this->app_settings){
             $pages =  json_decode($this->app_settings,true);
             foreach($pages  as $key=>$data){
                $data['image']           = show_image(file_path()['onboarding_image']['path'].'/'.$data['image']);
                $onboarding_data[$key]   = $data;
             }
    
        }

        
       $otpConfiguration = $this->otp_configuration ? @json_decode($this->otp_configuration) : null;

       return [
           'onboarding_pages'    => $this->app_settings ? array_values($onboarding_data)  : (object)[],
           "site_name"           => $this->site_name,
           'site_logo'           => show_image(file_path()['site_logo']['path'].'/site_logo.png'),
           'cash_on_delevary'    => $this->cod,
           'address'             => $this->address,
           'copyright_text'      => $this->copyright_text,
           "primary_color"       => $this->primary_color,
           "font_color"          => $this->font_color,
           "secondary_color"     => $this->secondary_color,
           "email"               => $this->mail_from,
           "phone"               => $this->phone,
           "guest_checkout"      => $this->guest_checkout == 1 ?  true : false,
           "order_id_prefix"     => $this->order_prefix,
           "filter_min_range"    => round( $this->search_min,2),
           "filter_max_range"    => round($this->search_max,2),
           "google_oauth"        => json_decode($this->s_login_google_info,true)['g_status'] == 1 ? json_decode($this->s_login_google_info,true) : (object) [],
           "facebook_oauth"      => json_decode($this->s_login_facebook_info,true)['f_status'] == 1 ?  json_decode($this->s_login_facebook_info,true) :  (object) [],

           'phone_otp'           => @$otpConfiguration && @$otpConfiguration->phone_otp == 1  ? true : false,
           'email_otp'           => @$otpConfiguration && @$otpConfiguration->email_otp == 1  ? true : false,
           'login_with_password' => @$otpConfiguration && @$otpConfiguration->login_with_password == 1  ? true : false,
       ];

    }


}
