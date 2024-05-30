@extends('frontend.layouts.app')
@section('content')


<div class="breadcrumb-banner">
    <div class="breadcrumb-banner-img">
        <img src="{{show_image(file_path()['frontend']['path'].'/'.@frontend_section_data($breadcrumb->value,'image'),@frontend_section_data($breadcrumb->value,'image','size'))}}" alt="breadcrumb.jpg">
    </div> 

    <div class="page-Breadcrumb">
        <div class="Container">
            <div class="breadcrumb-container">
                <h1 class="breadcrumb-title">{{($title)}}</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{url('/')}}">
                            {{translate('home')}}
                        </a></li>

                        <li class="breadcrumb-item active" aria-current="page">
                            {{translate($title)}}
                        </li>

                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<section class="pb-80">
	<div class="Container">
		<div class="row g-4">
            <div class="col-xl-9 col-lg-8">
                <div class="digital-product-left">
                    <div class="row g-2 g-md-4 ">
                        @foreach($digital_product->digitalProductAttribute->where('status', 1) as $digitalproduct)
                            <div class="col-sm-6">
                                <div class="attr-item digital-product-item" data-price="{{($digitalproduct->price)}}" data-id="{{$digitalproduct->id}}">
                                    <div class="form-check card-radio">
                                        <input id="{{$digitalproduct->id}}-{{$digitalproduct->id}}" name="attr-item05" type="radio" class="form-check-input">
                                        <label class="form-check-label bg--white border-0" for="{{$digitalproduct->id}}-{{$digitalproduct->id}}">
                                            <span class="attr-item-content">
                                                <span class="attr-item-img">
                                                    <img src="{{show_image(file_path()['product']['featured']['path'].'/'.$digital_product->featured_image,file_path()['digital_product']['featured']['size'])}}" alt="{{$digital_product->featured_image}}" />
                                                </span>

                                                <span class="attr-item-details">
                                                    <h4>{{$digitalproduct->name}}</h4>
                                                    <small class="digital-product-discount">{{$digitalproduct->short_details}}</small>
                                                </span>

                                                <span class="attr-item-price">
                                                    <span>{{show_currency()}}{{short_amount($digitalproduct->price,2)}}</span>
                                                </span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="card pd-description-tab mt-md-5 mt-4">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <h4 class="card-title">
                                        {{translate("Description")}}
                                    </h4>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="description-content">
                                 @php echo $digital_product->description @endphp
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-4">
                <div class="digital-product-right">
                    <div class="card">
                       <div class="card-header">
                           <div class="d-flex">
                               <div class="flex-grow-1">
                                   <h5 class="card-title mb-0">
                                     {{translate('Total')}}
                                   </h5>
                               </div>
                               <div>
                                  {{show_currency()}}<span id="total">0</span>
                               </div>
                           </div>
                       </div>

                       <form action="{{route('user.digital.product.order')}}" method="POST">
                                @csrf
                                <input type="hidden" name="digital_attribute_id">
                                <input type="hidden" name="digital_product_id" value="{{$digital_product->id}}">

                            <div class="card-body">
                                <div class="digital-product-calculate">
                                       
                                        @if(!auth()->user())
                                            <div class="mb-4 p-4 custom-email-input">
                                                <label class="form-label" for="email">
                                                     {{translate("Email")}} <span class="text-danger">*</span>
                                                </label>
                                                <input class="form-control" type="email" id="email" name="email" placeholder="{{translate('Enter your email')}}">
                                            </div>
                                        @endif

                                        <div class="d-flex flex-column gap-3 mb-4 mb-lg-5">
                                            @foreach($paymentMethods as $paymentMethod)

                                                <div class="form-check card-radio ps-0">
                                                    <input id="{{$paymentMethod->id}}+{{$paymentMethod->id}}" value="{{$paymentMethod->unique_code}}" name="payment_type" type="radio" class="form-check-input">
                                                    <label class="form-check-label pointer" for="{{$paymentMethod->id}}+{{$paymentMethod->id}}">
                                                        {{$paymentMethod->name}}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="digital-product-total-btn">
                                            <button   class="btn total-btn-buy-now">
                                                {{translate("BUY NOW")}}
                                             </button>
                                        </div>

                                </div>
                            </div>
                       </form>
                    </div>

                    <div class="product-shop digital-product-shop mt-md-5 mt-4">
                        <div class="card w-100">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    {{translate("Related Product")}}
                                </h5>
                            </div>

                            <div class="card-body">
                                <div class="related-product">
                                    @foreach($digital_products as $digital)
                                        <a href="{{route('digital.product.details', [make_slug($digital->name), $digital->id])}}" class="related-card">
                                            <div class="related-card-img">
                                                <img src="{{show_image(file_path()['product']['featured']['path'].'/'.$digital->featured_image,file_path()['product']['featured']['size'])}}" alt="{{$digital->featured_image}}" />
                                            </div>
                                            <div>
                                                <h4 class="product-title">{{$digital->name}}</h4>
                                                <small class="fs-12 text-muted">{{get_date_time($digital->created_at)}}</small>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
		</div>
	</div>
</section>

@endsection


@push('scriptpush')
<script>
	'use strict';
	$(document).on('click','.digital-product-item', function(){
		var price = $(this).data('price');
        price = parseFloat(price).toFixed(2);
		var id = $(this).data('id');
		$('input[name=digital_attribute_id]').val(id);
		$("#total").text(price);
	    $("#digitalattribute").find('.digital-product-item').removeClass('digital-product-active');
	    $(this).addClass('digital-product-active');
	});
</script>
@endpush
