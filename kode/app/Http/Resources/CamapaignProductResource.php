<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Models\ShippingDelivery;
use Illuminate\Http\Resources\Json\JsonResource;

class CamapaignProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
    

        $product =  Product::where('id',$this->product_id)->first();

        $reviews =  [];

        if($product->review){

            $reviewData  = array();
            foreach($product->review as $review){
                $reviewData ['user']    = $review->customer?->name;
                $reviewData ['profile'] = show_image(file_path()['profile']['user']['path'].'/'.$review->customer?->image);
                $reviewData ['review']  = $review->review;
                $reviewData ['rating']  = (int) $review->rating ;
            }
            array_push($reviews, $reviewData);
        }

        $discount  = api_short_amount(discount($product->stock->first()->price,$this->discount,$this->discount_type));

        $gallery_image = [];
        foreach($product->gallery as $gallery){
            array_push( $gallery_image, show_image(file_path()['product']['gallery']['path'].'/'.$gallery->image));
        }

        $varient =  [];
        foreach (json_decode($product->attributes_value) as $key => $attr_val){
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

        foreach($product->stock as $stock){

            $varient_discount  = api_short_amount(discount($stock->price,$this->discount,$this->discount_type));
            $varient_price [$stock->attribute_value] = [

                'qty'      => $stock->qty,
                'price'    => api_short_amount($stock->price),
                'discount' =>   $varient_discount
            ];
        }


        $shipping_data =  [];
        if($product->shippingDelivery){

            $ids            = $product->shippingDelivery
                                      ->pluck("shipping_delivery_id")
                                      ->toArray();

            $shipping_data  = ShippingDelivery::whereIn('id',$ids )->get();
        }


        return [
            'uid'                 => $product->uid,
            'name'                => $product->name,
            'order'               => $product->order->count(),
            'price'               => api_short_amount($product->stock->first()->price),
            'discount_amount'     => $discount,
            'short_description'   => $product->short_description,
            'description'         => $product->description,

            'brand'               => $product->brand? json_decode( $product->brand->name,true) :(object)[], 
            'category'            => $product->category? json_decode($product->category->name,true) : (object)[], 
            'rating'              =>    [
                                            'total_review' => count($product->review),
                                            'avg_rating' =>  $product->review->avg('rating') ? round($product->review->avg('rating')) :0 ,
                                            'review' => count($product->review) > 0 ? ($reviews) : (object)[]
                                        ],
            'featured_image'      => show_image(file_path()['product']['featured']['path'].'/'.$product->featured_image),
            'gallery_image'       => $gallery_image ,
            'varient'             => $varient,
            'varient_price'       => $varient_price,
            'shipping_info'       => new ShippingCollection(  $shipping_data),
            'url'                 => route('product.details',[make_slug($this->name),$this->id])
        ];
    }
}
