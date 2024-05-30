<?php

namespace App\Http\Resources;

use App\Models\Campaign;
use App\Models\CampaignProduct;
use App\Models\ShippingDelivery;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class ProductResource extends JsonResource
{


 
    public $campaignProduct = null;

    public function campaign($campaignProduct){
        $this->campaignProduct =  $campaignProduct;
    }
   
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {



        $discount  = api_short_amount(cal_discount(@$this->discount_percentage?? 0,@$this->stock->first()->price));

        if($this->pivot){
            $discount  = api_short_amount(discount($this->stock->first()->price,$this->pivot->discount,$this->pivot->discount_type));
        }
     

        $gallery_image = [];

        foreach($this->gallery as $gallery) {
            array_push( $gallery_image, show_image(file_path()['product']['gallery']['path'].'/'.$gallery->image));
        }

        $varient =  [];
        foreach (json_decode($this->attributes_value) as $key => $attr_val){
            $stock =  [];
            $attributeOption =  \App\Models\Attribute::find($attr_val->attribute_id); 
            if( $attributeOption){
                foreach ($attr_val->values as $key => $value){
                    array_push($stock,$value);
                }
                $varient[ $attributeOption->name ] = $stock;
            }
        }
        $varient_price = [];

        foreach($this->stock as $stock){


            $varient_discount      = api_short_amount(cal_discount($this->discount_percentage,$stock->price));

            if($this->pivot){
                $varient_discount  = api_short_amount(discount($stock->price,$this->pivot->discount,$this->pivot->discount_type));
            }
            
            
            $varient_price [$stock->attribute_value] = [
                'qty'      => $stock->qty,
                'price'    => api_short_amount($stock->price),
                'discount' =>   $varient_discount
            ];
        }

        $shipping_data =  [];

        if($this->shippingDelivery){
            $ids             = $this->shippingDelivery->pluck("shipping_delivery_id")->toArray();
            $shipping_data   = ShippingDelivery::whereIn('id',$ids )->get();
           
        }

        $reviews =  [];

        if($this->review){
            $reviewData  = array();
            foreach($this->review as $review){
                $reviewData ['user']    = $review->customer?->name;
                $reviewData ['profile'] = show_image(file_path()['profile']['user']['path'].'/'.$review->customer?->image);
                $reviewData ['review']  = $review->review;
                $reviewData ['rating']  = (int) $review->rating ;
            }
            array_push($reviews, $reviewData);
        }

        return [
            'uid'                   => $this->uid,
            'name'                  => $this->name,
            'order'                 => $this->order->count(),
            'brand'                 => $this->brand? json_decode( $this->brand->name,true) :(object)[], 
            'category'              => $this->category? json_decode( $this->category->name,true) : (object)[], 
            'price'                 => api_short_amount($this->stock->first()->price),
            'discount_amount'       =>  $discount,
            'short_description'     => $this->short_description,
            'description'           => $this->description,
            'maximum_purchase_qty'  => $this->maximum_purchase_qty,
            'minimum_purchaseqty'   => $this->minimum_purchase_qty,
            'rating' => [
                'total_review' => count($this->review),
                'avg_rating'   =>  $this->review->avg('rating') ? round($this->review->avg('rating')) :0 ,
                'review'       => count($this->review) > 0  ? ($reviews) : (object)[]
            ],
            'featured_image'   => show_image(file_path()['product']['featured']['path'].'/'.$this->featured_image),
            'gallery_image'    => $gallery_image ,
            'varient'          =>  $varient,
            'varient_price'    =>  $varient_price,
            'shipping_info'    =>  new ShippingCollection(  $shipping_data),
            'url'              => route('product.details',[make_slug($this->name),$this->id])
        ];
    
    }
}
