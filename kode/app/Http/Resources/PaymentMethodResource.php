<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class PaymentMethodResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        $paymentMethods = [
            'STRIPE101'      => 'stripe',
            'BKASH102'       => 'bkash',
            'PAYSTACK103'    => 'paystack',
            'NAGAD104'       => 'nagad',
            'PAYPAL102'      => 'paypal',
            'FLUTTERWAVE105' => 'flutterwave',
            'RAZORPAY106'    => 'razorpay',
            'INSTA106'       => 'instamojo',
        ];

        $method = Arr::get($paymentMethods , $this->unique_code); 

        return [
            'percent_charge'       => $this->percent_charge,
            'currency'             => $this->currency,
            'rate'                 => $this->rate,
            'name'                 => $this->name,
            'unique_code'          => $this->unique_code,
            'payment_parameter'    => ($this->payment_parameter),
            'image'                => show_image(file_path()['payment_method']['path'].'/'.$this->image),
            'callback_url'         => route($method.".callback")
        ];


    }
}
