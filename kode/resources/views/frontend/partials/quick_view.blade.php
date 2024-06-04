<div class="quick-view-container product-details-container">
    <div class="product-detail-left pe-lg-5">
        <div class="product-thumbnail-slider">
                <img class="qv-lg-image" src="{{show_image(file_path()['product']['gallery']['path'].'/'.$product->gallery->first()->image,file_path()['product']['gallery']['size'])}}" alt="{{@$product->gallery->first()->image}}">
        </div>
        <div class="small-img">
            <div class="small-img-item">
                @foreach($product->gallery as $gallery)
                    <div class="gallery-sm-img quick-view-img">
                        <img src="{{show_image(file_path()['product']['gallery']['path'].'/'.$gallery->image,file_path()['product']['gallery']['size'])}}" alt="{{$gallery->image}}">
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="product-detail-middle">
        <h3 class="details-product-title">
            {{($product->name)}}
        </h3>
        <div class="product-item-review">
            <div class="ratting mb-0">
               @php echo show_ratings($product->rating()->avg('rating')) @endphp <small>({{$product->rating()->count()}} {{translate('Reviews')}})</small>
            </div>
            <small>{{$product->order->count()}}
                {{translate("Orders")}}
            </small>
        </div>
        <div class="product-item-price  price-section">
            @if(count($product->campaigns) != 0 && $product->campaigns->first()->end_time > Carbon\Carbon::now()->toDateTimeString() &&   $product->campaigns->first()->status == '1')
                        @if(($product->campaigns->first()->pivot->discount) == 0)
                              <span>{{show_currency()}}{{number_format(short_amount($product->price),2)}}</span>
                        @else

                            <span>
                                {{show_currency()}}{{number_format(short_amount(discount($product->stock->first()?$product->stock->first()->price:$product->price,$product->campaigns->first()->pivot->discount,$product->campaigns->first()->pivot->discount_type)),2)}}
                            </span>
                            <del>
                                {{show_currency()}}{{number_format(short_amount($product->stock->first()->price),2)}}
                            </del>
                        @endif
                    @else

                    @if(($product->discount_percentage) > 0)
                        <span>
                            {{show_currency()}}{{short_amount(cal_discount($product->discount_percentage,$product->stock->first()->price))}}
                        </span>  <del>
                            {{show_currency()}}{{short_amount($product->stock->first()?$product->stock->first()->price:$product->price)}}</del>

                        @else
                        <span>
                            {{show_currency()}}{{short_amount($product->stock->first()?$product->stock->first()->price:$product->price)}}
                        </span>

                    @endif
            @endif
        </div>
        <div class="product-item-summery">
            @php echo $product->short_description @endphp
        </div>
        @php
            $randNum = rand(5,99999999);
            $randNum = $randNum."details".$randNum;
        @endphp
        <form class="attribute-options-form-{{$randNum}} quick-view-form">
            <input type="hidden" name="id" value="{{ $product->id }}">
            @if(count($product->campaigns) != 0 && $product->campaigns->first()->end_time > Carbon\Carbon::now()->toDateTimeString() &&   $product->campaigns->first()->status == '1')
               <input type="hidden" name="campaign_id" value="{{ $product->campaigns->first()->id }}">
            @endif
            @foreach (json_decode($product->attributes_value) as $key => $attr_val)

                @php
                    $attributeOption =  \App\Models\Attribute::find($attr_val->attribute_id);
                @endphp

                <div class="product-colors">
                    <span> {{ $attributeOption->name }}:</span>
                    <div class="variant">
                        @foreach ($attr_val->values as $key => $value)
                            <div class="variant-item">
                                <input @if ($key == 0) checked @endif type="radio" class="btn-check attribute-select"   name="attribute_id[{{ $attr_val->attribute_id }}]" value="{{$value}}" id="success-outlined-{{$value}}" autocomplete="off">
                                <label class="btn-outline-success variant-btn" for="success-outlined-{{$value}}">{{ $value }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
            @php 
               $stockQty = (int) @$product->stock->first()->qty ??  0;
            @endphp
            <div class="stock-status" id="quick-view-stock">
                @if($stockQty > 0)
                    <div class="instock">
                        <i class="fa-solid fa-circle-check"></i>
                        <p>
                            {{translate("In Stock")}}
                        </p>
                    </div>
                @else
                    <div class="outstock">
                        <i class="fas fa-times-circle"></i>
                        <p>
                            {{translate("Stock out")}}
                        </p>
                    </div>
                @endif
           </div>
            <div class="product-actions-type">
                <div class="input-step">
                    <button type="button" class="update_qty x decrement ">–</button>
                    <input type="number" data-view='quick-view' id="quantity" class="quick-view-quantity product-quantity"  name="quantity" value="1" min='0'>
                    <button type="button" class="update_qty y increment ">+</button>
                </div>
                @php
                    $authUser = auth_user('web');
                    $wishedProducts = $authUser ? $authUser->wishlist->pluck('product_id')->toArray() : [];
                @endphp
                <a href="javascript:void(0)"  data-product_id = '{{$randNum }}' class="buy-now addtocartbtn">
                    <i class="fa-solid fa-cart-shopping"></i>
                </a>
                <button data-product_id ="{{$product->id}}" class="product-details-love-btn wishlistitem">
                    <i class="@if(in_array($product->id,$wishedProducts))
                        fa-solid
                    @else
                        fa-regular
                    @endif fa-heart"></i>
                </button>
                <button class="product-details-love-btn comparelist wave-btn" data-product_id="{{$product->id}}"><i class="fa-solid fa-code-compare"></i></button>
            </div>
        </form>
        <div class="product-detail-btn">
            <a href="javascript:void(0)" data-checkout = "yes" data-product_id = "{{$randNum}}" class="buy-now-btn quick-buy-btn addtocartbtn">
                {{translate("Buy Now")}}
            </a>
        </div>
        <div class="stock-and-social">
            <div class="product-details-social">
                <span> {{translate("Share")}}  :</span>
                <div class="product-details-social-link">
                    <a href="https://www.facebook.com/sharer.php?u={{urlencode(url()->current())}}" target="__blank"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="https://twitter.com/share?url={{urlencode(url()->current())}}&text=Simple Share Buttons&hashtags=simplesharebuttons" target="__blank"><i class="fa-brands fa-twitter"></i></a>
                    <a href="http://www.linkedin.com/shareArticle?mini=true&url={{urlencode(url()->current())}}" target="__blank"><i class="fa-brands fa-linkedin"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>












