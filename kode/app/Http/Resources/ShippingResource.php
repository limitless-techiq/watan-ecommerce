<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ShippingResource extends JsonResource
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
            "uid"          => $this->uid,
            'method_name'  => $this->method->name,
            'duration'     => $this->duration,
            'price'        => api_short_amount($this->price,2),
            'description'  => $this->description,
            'image'        => show_image(file_path()['shipping_method']['path'].'/'.$this->method->image)
        ];
 
    }
}
