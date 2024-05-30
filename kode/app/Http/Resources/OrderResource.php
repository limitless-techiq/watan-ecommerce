<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

       
        $status = null;
        if($this->status == '1'){
             $status = "Placed";
        }
        elseif($this->status == '2'){
             $status = "Confirmed";
        }
        elseif($this->status == '3'){
             $status = "Processing";
        }
        elseif($this->status == '4'){
             $status = "Shipped";
        }
        elseif($this->status == '5'){
             $status = "Delivered";
        }
        elseif($this->status == '6'){
             $status = "Cancel";
        }
        $paymentMethod  =$this->paymentMethod ? $this->paymentMethod->name : null;

        return [
            'uid'                 => $this->uid,
            'order_id'            => $this->order_id,
            'order_date'          => $this->created_at,
            'quantity'            => $this->qty,
            'shipping_charge'     => api_short_amount($this->shipping_charge,2),
            'discount'            => api_short_amount($this->discount,2),
            'amount'              => api_short_amount($this->amount,2),
            'payment_type'        => $this->payment_type == '1' ? 'Cash On Delivary' : $paymentMethod,
            'payment_status'      => $this->payment_status == '1' ? 'Unpaid' :"Paid",
            'shipping_method'     => $this->shipping ? $this->shipping->method->name : null,
            'status'              => $status,
            'status_log'          => OrderStatusResource::collection($this->orderStatus->whereNotNull('delivery_status')),
            'order_details'       => count($this->orderDetails)!= 0 ?  new TransactionDetailsCollection($this->orderDetails) : [],
            'billing_information' => $this->billing_information
        ];
    }
}
