<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
class ApiLang
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
            if($request->hasHeader('api-lang')){
                $locale = $request->header('api-lang');
            }
            else{
                $locale = (Language::where('is_default',"1")->first())->code;
            }
            App::setLocale($locale);
        } catch (\Throwable $th) {
     
        }
        return $next($request);
    }
}
