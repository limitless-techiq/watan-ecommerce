<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DigitalProductResource extends JsonResource
{

    protected $digitalProductAttributeId;

    public function __construct($resource, $digitalProductAttributeId =   null )
    {
        parent::__construct($resource);
        $this->digitalProductAttributeId = $digitalProductAttributeId;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $attribute_options =  [];


        foreach ($this->digitalProductAttribute as $attribute) {

            if ($this->digitalProductAttributeId == $attribute->id) {
                $attribute_options[$attribute->name] = [
                    'uid' => $attribute->uid,
                    'price' => api_short_amount($attribute->price),
                    'short_details' => $attribute->short_details,
                    'product_id' => $attribute->product_id,
                ];
                break;
            } elseif ($attribute->status == '1') {
                $attribute_options[$attribute->name] = [
                    'uid' => $attribute->uid,
                    'price' => api_short_amount($attribute->price),
                    'short_details' => $attribute->short_details,
                    'product_id' => $attribute->product_id,
                ];
            }
        }

   
        $price = api_short_amount($this->digitalProductAttribute 
                                           ? @optional($this->digitalProductAttribute())->where('status','1')->orWhere('id',$this->digitalProductAttributeId)->first()?->price
                                           :0);
        return [
            
            'uid'                => $this->uid,
            'name'               => $this->name,
            'attribute'          => (object)$attribute_options  ,
            'price'              => $price,
            'short_description'  => $this->short_description,
            'description'        => $this->description,
            'featured_image'     => show_image(file_path()['product']['featured']['path'].'/'.$this->featured_image),
            'url'                => route('digital.product.details', [make_slug($this->name), $this->id])
        ];
    }
}
