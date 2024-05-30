<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $status ='';
        
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

        return [
            'uid'         => $this->uid,
            'product'     => $this->digital_product_attribute_id != null 
                                     ? new DigitalProductResource($this->product ,$this->digital_product_attribute_id ) 
                                     : new ProductResource($this->product),
            
            'order_id'    => $this->order_id,
            'quantity'    => $this->quantity,
            'total_price' => api_short_amount($this->total_price,2),
            'attribute'   => $this->attribute 
                               ? $this->attribute  
                               : null,
             
            'status'      => $status
        ];
    }
}
