<?php

namespace App\Http\Middleware;

use App\Models\Currency;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
 
class CurrencyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
 
        try {
            if($request->hasHeader('currency-uid') && $request->header('currency-uid')){
                $currency = Currency::where('uid',$request->header('currency-uid'))->first();
            }
            else{
                $currency =  Currency::where('name', general_setting('currency')->currency_name)->first();
            }
    
            $currency_data = Cache::rememberForever('currency_data', function () use($currency) {
                return $currency ;
            });
            if($currency_data->symbol !=   $currency->symbol){
                
                Cache::forget('currency_data');
                $currency_data = Cache::rememberForever('currency_data', function () use($currency) {
                    return $currency ;
                });
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    
        return $next($request);
    }
}

