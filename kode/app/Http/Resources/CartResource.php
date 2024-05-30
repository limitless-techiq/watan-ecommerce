<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
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
            'uid'      => $this->uid,
            'pirce'    => api_short_amount($this->price),
            'qty'      => $this->quantity,
            'total'    => api_short_amount($this->total),
            'varitent' => $this->attributes_value,
            'product'  => new ProductResource($this->product)
        ];
    }
}
