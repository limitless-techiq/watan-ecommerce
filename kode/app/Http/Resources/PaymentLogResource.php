<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

          return [
            "payment_method"   => $this->paymentGateway ? new PaymentMethodResource($this->paymentGateway) : null,
            'uid'              => $this->uid,
            'trx_number'       => $this->trx_number,
            'amount'           => api_short_amount($this->amount,2),
            'charge'           => api_short_amount($this->charge,2),
            'payable'          => api_short_amount($this->charge + $this->amount,2),
            'exchange_rate'    => round($this->rate,2),
            'final_amount'     => round($this->final_amount,2),
        ];
    }
}
