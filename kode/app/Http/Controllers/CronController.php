<?php

namespace App\Http\Controllers;

use App\Models\GeneralSetting;
use Illuminate\Http\Request;
use App\Models\PlanSubscription;
use App\Models\Product;
use Carbon\Carbon;
use DateTime;
class CronController extends Controller
{
    public function handle()
    {

        $products = Product::where('product_type','102')->whereNull('seller_id')->where('status','0')->cursor();
        foreach( $products  as $product){

            $current_date = new DateTime();
            $created_date = new DateTime($product->created_at);
            $interval = $created_date->diff($current_date);
            if($interval->days > general_setting()->status_expiry ){
                $product->status =  1;
                $product->save();
            }
        }

        $subscriptions = PlanSubscription::where('status', PlanSubscription::RUNNING)->get();
        foreach($subscriptions as $subscription){
            $expiredTime = $subscription->expired_date->addDays($subscription->plan->duration); 
            $now = Carbon::now()->toDateTimeString();
            if($now > $expiredTime){
                $subscription->status = PlanSubscription::EXPIRED;
                $subscription->save();
            }
        }


        $general = GeneralSetting::first();
        $general->last_cron_run = Carbon::now();
        $general->save();
   
    }
}
