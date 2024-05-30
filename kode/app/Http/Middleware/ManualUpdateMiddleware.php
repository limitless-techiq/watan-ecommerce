<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Response as HttpResponse;

class ManualUpdateMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     */
    public function handle(Request $request, Closure $next) :Response
    {
        try {
            $baseURL = url('/');

            $configFilePath = $baseURL.'/'.'manual.json';
            $configJson     = json_decode(file_get_contents($configFilePath), true);

            $general        = general_setting();



            if(is_array($configJson) && Arr::get($configJson , 'is_manual' , false) && (double)(Arr::get($configJson , 'version' , 1.0)) >(double)$general->app_version ?? 1.0 ){

                 if ($request->expectsJson() || $request->isXmlHttpRequest() || $request->is('api/*') ) {
                    return api([
                        'errors' => ['Please update your application']])->fails(__('response.fail'),HttpResponse::HTTP_FORBIDDEN ,2000000);

                 }

                return redirect()->route('admin.manual.update');

            }

    

        } catch (\Throwable $th) {
            //throw $th;
        }
        return $next($request);
    }
}
