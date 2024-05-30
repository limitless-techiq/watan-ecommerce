<?php

namespace App\Http\Middleware;

use App\Models\Currency;
use Closure;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
class LanguageMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */

    public function handle($request, Closure $next)
    {
        try {
            if(session()->has('locale')){
                $locale = session()->get('locale');
            }
            else{
                $locale = (Language::where('is_default',"1")->first())->code;
            }
            App::setLocale($locale);
            session()->put('locale', $locale);
    
            $this->systeCurrency();
        } catch (\Throwable $th) {
           
        }
        return $next($request);
    }

    public function systeCurrency(){
        if(session()->has('currency')){
            $currency = session()->get('currency');
        }
        else{
            $currency = (Currency::where('default',"1")->first())->id;
        }
        session()->put('currency', $currency);
    }

   
}
