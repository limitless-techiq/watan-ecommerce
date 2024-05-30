<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            
            'uid'             => $this->uid,
            'id'              => $this->id,
            'name'            => $this->name,
            'username'        => $this->username,
            'email'           => $this->email,
            'phone'           => $this->phone,
            'image'           => show_image(file_path()['profile']['user']['path'].'/'.$this->image),
            'address'         => $this->address ? $this->address : (object)[],
            'billing_address' => $this->billing_address ? $this->billing_address : (object)[],
   
            
        ];
    }
}
