<?php

namespace App\Http\Middleware;

use App\Models\GeneralSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Routing\Route;
class SellerModeCheck
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
        $genSet =  GeneralSetting::first();

 

        if($genSet->seller_mode ==  'inactive'){
           
            if(Auth::guard('admin')->user())
            {
                return redirect()->route('admin.dashboard')->with('error','Seller Panel is Currently Inactive');
            }
            else{
                return redirect()->route('home')->with('error','Seller Panel Is Currently Inactive');
            }

        }
        return $next($request);
    }
}
