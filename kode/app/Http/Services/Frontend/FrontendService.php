<?php

namespace App\Http\Services\Frontend;

use App\Enums\StatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

class FrontendService extends Controller
{


    // change language
    public function changeLang($code ,$api = false){
        $response['status'] = "success";
        $response['message'] = translate('Language Switched Successfully');

        if(!Language::where('code', $code)->exists()){
            $code = 'en';
        }
        if(!$api){
            $currentLang  = session()->get('locale');
            optimize_clear();
            session()->put('locale', $code);
        }
        App::setLocale($code);
        return $response;

    }


    //get all active lang

    public function language(){
        return Language::active()->get();
    }
}