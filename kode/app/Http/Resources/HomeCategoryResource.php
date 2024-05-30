<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeCategoryResource extends JsonResource
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
    
            'title'                    => $this->title,
            'category'                 => new CategoryResource($this->category),
            'products'                 => new ProductCollection( Product::with(['review','order','stock','stock.attribute'])->where('category_id', $this->category_id)->inhouseProduct()->whereIn('status',[0,1])->physical()->take(10)->get()),
          
        ];
        
     
    }
}
