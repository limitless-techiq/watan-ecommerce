<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Artisan;
use App\Rules\General\FileExtentionCheckRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GeneralSettingController extends Controller
{

    public function __construct(){
        $this->middleware(['permissions:view_support'])->only('index');
        $this->middleware(['permissions:update_settings'])->only('socialLoginUpdate','updateSellerMode','updateDebugMode');
    }


    /**
     * Get setting view
     *
     * @return View
     */
    public function index() :View {
        $title   = "System Setting";
        $general = GeneralSetting::first();
        return view('admin.setting.index', compact('title', 'general'));
    }


    /**
     * AI Configuration
     *
     * @return View
     */
    public function aiConfiguration() :View {

        $title   = "AI Configuration";
        $general = GeneralSetting::first();
        return view('admin.ai_configuration', compact('title', 'general'));
    }

    /**
     * AI Configuration
     *
     * @return View
     */
    public function aiConfigurationUpdate(Request $request) :RedirectResponse {

 
        $general = GeneralSetting::first();

        $general->open_ai_setting = json_encode($request->input('site_settings',[]));

        $general->save();

        return redirect()->back()->with('success',translate('AI Configuration Updated'));

    }


    

    /**
     * Update general settings
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request) : RedirectResponse{

        if($request->has('banner_section')){
             $this->validate($request,[
                'banner_section' => 'required'
             ]);
        }
        else{
            $this->validate($request, [
                'site_name'       => 'required|max:255',
                'copyright_text'  => 'required',
                'currency_name'   => 'required|max:10',
                'currency_symbol' => 'required|max:10',
                'site_logo'       => 'nullable|image|mimes:jpg,png,jpeg',
                'loder_logo'      => ['nullable','image',new FileExtentionCheckRule(file_format())],
                'invoice_logo.*'  => 'nullable|image|mimes:jpg,png,jpeg',
                'admin_site_logo' => 'nullable|image|mimes:jpg,png,jpeg',
                'site_favicon'    => 'nullable|image|mimes:jpg,png,jpeg',
                'primary_color'   => 'nullable', 'regex:/^[a-f0-9]{6}$/i',
                'secondary_color' => 'nullable', 'regex:/^[a-f0-9]{6}$/i',
            ]);
        }
       

        $general = GeneralSetting::first();

        if($request->has('banner_section')){
            $general->banner_section = $request->banner_section;
        }
        else{
            $general->site_name          = $request->site_name;
            $general->commission         = $request->commission;
            $general->refund             = $request->refund;
            $general->refund_time_limit  = $request->refund_time_limit;
            $general->cod                = $request->cod;
            $general->email_notification = $request->email_notification;
            $general->currency_name      = $request->currency_name;
            $general->currency_symbol    = $request->currency_symbol;
            $general->search_min         = $request->search_min;
            $general->search_max         = $request->search_max;
            $general->strong_password    = $request->strong_password;
            $general->maintenance_mode   = $request->maintenance_mode;
            $general->order_prefix       = make_slug($request->order_prefix);
            $general->primary_color      = $request->primary_color;
            $general->secondary_color    = $request->secondary_color;
            $general->font_color         = $request->font_color;
            $general->copyright_text     = $request->copyright_text;
            $allInvoiceLogos             = json_decode($general->invoice_logo,true);
            $general->mail_from          = $request->mail_from;
            $general->status_expiry      = $request->status_expiry;
            $general->seller_reg_allow   = $request->seller_reg_allow;
            $general->guest_checkout     = $request->guest_checkout;
            $general->address            = $request->address;
            $general->phone              = $request->phone;
            $general->preloader          = $request->preloader;
            $general->otp_configuration          = $request->otp_configuration;
     
       
            if($request->hasFile('invoice_logo')){
                $invoiceLogos = $request->invoice_logo;
                foreach($invoiceLogos as $key=>$value){
                    $allInvoiceLogos[$key] =  store_file($value, file_path()['invoiceLogo']['path'], null, $allInvoiceLogos[$key]);
                }
                $general->invoice_logo     = json_encode($allInvoiceLogos);
            }
            
           
            if($request->hasFile('site_logo')) {
                try{
                    $site_logo          =  $general->site_logo;
                    $general->site_logo =  store_file($request->site_logo, file_path()['site_logo']['path'], null,  $site_logo);

                }catch (\Exception $exp) {
                    return back()->with('error',strip_tags($exp->getMessage()));
                }
            }
            if($request->hasFile('admin_site_logo')) {
    
                try{
                    $site_logo              =  $general->admin_logo_lg;
                    $general->admin_logo_lg =  store_file($request->admin_site_logo, file_path()['admin_site_logo']['path'], null,  $site_logo);
                }catch (\Exception $exp) {
    
                      return back()->with('error',strip_tags($exp->getMessage()));
                }
            }
            
            if($request->hasFile('admin_site_logo_sm')) {
    
                try{
                    $site_logo              =  $general->admin_logo_sm;
                    $general->admin_logo_sm =  store_file($request->admin_site_logo_sm, file_path()['admin_site_logo']['path'], null,  $site_logo);
                }catch (\Exception $exp) {
        
                    return back()->with('error',strip_tags($exp->getMessage()));
                }
            }
            if($request->hasFile('site_favicon')) {
                try {
                    $favicon               =  $general->site_favicon;
                    $general->site_favicon =  store_file($request->site_favicon, file_path()['site_logo']['path'], null,  $favicon);
                }catch (\Exception $exp) {
                    return back()->with('error',strip_tags($exp->getMessage()));
                }
            }
    
        }
       
        $general->save();

        return back()->with('success',translate('General Setting has been updated'));
    }


    /**
     * Optimize clear
     *
     * @return RedirectResponse
     */
    public function cacheClear() :RedirectResponse
    {
        Artisan::call('optimize:clear');
        return back()->with('success',translate('Cache cleared successfully'));
    }


    /**
     * System overview
     *
     * @return View
     */
    public function systemInfo() :View
    {

        $systemInfo = [
            'laravel_version' => app()->version(),
            'server_detail'   => $_SERVER,
        ];
        return view('admin.system_info',[
            
            'title'           => "Server Information",
            'systemInfo'      =>  $systemInfo
        ]);
    }


    /**
     * Social Login settings
     *
     * @return View
     */
    public function socialLogin() :View
    {
        $title = "Social Login Credentials";
        return view('admin.setting.socal_login', compact('title'));
    }


    /**
     * Social login setting update
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function socialLoginUpdate(Request $request) :RedirectResponse
    {
        $this->validate($request, [
            'g_client_id'     => 'required',
            'g_client_secret' => 'required',
            'g_status'        => 'required|in:1,2',
            'f_client_id'     => 'required',
            'f_client_secret' => 'required',
            'f_status'        => 'required|in:1,2',
        ]);
        $general = GeneralSetting::first();
        $google = [
            'g_client_id'     => $request->g_client_id,
            'g_client_secret' => $request->g_client_secret,
            'g_status'        => $request->g_status,
        ];
        $facebook = [
            'f_client_id'     => $request->f_client_id,
            'f_client_secret' => $request->f_client_secret,
            'f_status'        => $request->f_status,
        ];
        $general->s_login_google_info   = $google;
        $general->s_login_facebook_info = $facebook;
        $general->save();

        return back()->with('success',translate('Social login setting has been updated'));
    }




    /**
     * Update selller mode
     *
     * @return string
     */
     public function updateSellerMode() :string {

        $general = GeneralSetting::first();
        $status  = 'active';
        if($general->seller_mode == 'active'){
            $status = 'inactive';
        }

        $general->seller_mode = $status;
        $general->save();
        return json_encode([
            'status'=> ucfirst($status)
        ]);
     }


     /**
      * Update debug mode
      *
      * @return string
      */
     public function updateDebugMode() :string {

        try {
            $path = base_path('.env');
            if (file_exists($path)) {
                if(env('APP_DEBUG')){
                    file_put_contents($path, str_replace(
                        "APP_DEBUG=true", "APP_DEBUG=false", file_get_contents($path)
                    ));
                }
                else{
                    file_put_contents($path, str_replace(
                        "APP_DEBUG=false", "APP_DEBUG=true", file_get_contents($path)
                    ));
                }
            }
        } catch (\Throwable $th) {
           
        }
      
        return json_encode([
            'status'=> true
        ]);
     }

     
     /**
      * Twak to setting 
      *
      * @return View
      */
     public function plugin() :View{
        $title     = "Plugin Settings";
        return view('admin.setting.tawk',compact('title'));
     }
  

     /**
      * Twak to update
      *
      * @param Request $request
      * @return RedirectResponse
      */
     public function pluginUpdate(Request $request) :RedirectResponse{
        $general          = GeneralSetting::first();
        $general->tawk_to =  json_encode($request->tawk,true);
        $general->save();
        return back()->with('success',translate('Plugin Settings Updated'));
     }

    

     /**
      * Flutter app setting Update
      *
      * @param Request $request
      * @return RedirectResponse
      */
     public function appSettingUpdate(Request $request) : RedirectResponse{

        $general = GeneralSetting::first();
        $size    = explode('x',  file_path()['onboarding_image']['size']);
        $rules   = [
            "setting.*.heading"     => "required",
            "setting.*.description" => "required",
            "setting.*.image"       => ["required" , 'image',new FileExtentionCheckRule(file_format())],
        ];

        $app_settings = $general->app_settings ? json_decode($general->app_settings,true) : [];
        if(count($app_settings) > 0){
            $rules ['setting.*.image'] =  ['image',new FileExtentionCheckRule(file_format())];
        }

        $data = [];
        $request->validate($rules);
            if($request->setting){
             foreach($request->setting as $key=>$settings){
                $data[$key]['image'] = null;
                $removeImage         = null;

                if(isset($app_settings[$key]['image'])){
                    $data[$key]['image']  = $app_settings[$key]['image'];
                    $removeImage          = $app_settings[$key]['image'];
                }
                foreach($settings as $sub_key=>$setting){
                    if($sub_key == "image"){
                        $data[$key][$sub_key] = store_file($setting, file_path()['onboarding_image']['path'],null,$removeImage);
                    }
                    else{
                        $data[$key][$sub_key] = $setting;
                    }
                }
            
             }
            }
  
        $general->app_settings = json_encode($data);
        $general->save();


        return back()->with('success',translate('Plugin Settings Updated'));

     }
     
     


     /**
      * Flutter app on boarding settings
      *
      * @return View
      */
     public function appSettings() :View{

        $title = "Flutter App Settings";
        return view('admin.setting.app_setting',compact('title'));
     }
}
