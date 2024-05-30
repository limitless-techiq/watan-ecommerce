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
    @php
        $paymentOptions = [];
        $flag = 0;
        foreach($items as $data){
            if($flag == 0){
                if($data->campaigns){
                    if($data->price && $data->price !=null &&  Carbon\Carbon::now()->toDateTimeString() < $data->campaigns->end_time &&  $data->campaigns->status == '1'){
                        if(is_array(json_decode($data->campaigns->payment_method))){
                            $paymentOptions = json_decode($data->campaigns->payment_method);
                            $flag = 1;
                        }
                    }
                }

            }
            else{
                break;
            }
        }
    @endphp
    <div class="Container">
        <form action="{{route('user.order')}}" method="POST">
            @csrf
            <div class="row g-4">
                <div class="col-xxl-9 col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                {{translate("Checkout")}}
                            </h5>
                        </div>

                        <div class="card-body checkout-tab">
                            <div class="step-arrow-nav">
                                <ul class="nav nav-pills nav-justified custom-nav" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link p-3 active wave-btn" id="pills-bill-info-tab"
                                        data-bs-toggle="pill" data-bs-target="#pills-bill-info"
                                        type="button" role="tab" aria-controls="pills-bill-info"
                                        aria-selected="true">
                                        <svg  version="1.1"   x="0" y="0" viewBox="0 0 32 32" xml:space="preserve" fill-rule="evenodd" ><g><path d="m25.961 28.749.039.001.032-.003c.144-.018.718-.128.718-.747A9.75 9.75 0 0 0 17 18.25h-2a9.75 9.75 0 0 0-9.748 9.796.664.664 0 0 0 .242.507.747.747 0 0 0 .506.197s.75-.043.75-.75A8.25 8.25 0 0 1 15 19.75h2a8.25 8.25 0 0 1 8.252 8.296.664.664 0 0 0 .242.507.746.746 0 0 0 .467.196z"  opacity="1" data-original="#000000"></path><path d="M16 3.25c-4.553 0-8.25 3.697-8.25 8.25s3.697 8.25 8.25 8.25 8.25-3.697 8.25-8.25S20.553 3.25 16 3.25zm0 1.5c3.725 0 6.75 3.025 6.75 6.75s-3.025 6.75-6.75 6.75-6.75-3.025-6.75-6.75S12.275 4.75 16 4.75z"  opacity="1" data-original="#000000"></path></g></svg>
                                            {{translate("Personal Info")}}
                                        </button>
                                    </li>

                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link p-3 wave-btn" id="pills-bill-address-tab"
                                            data-bs-toggle="pill" data-bs-target="#pills-bill-address"
                                            type="button" role="tab" aria-controls="pills-bill-address"
                                            aria-selected="false">
                                                <svg  version="1.1"   x="0" y="0" viewBox="0 0 512 512"   xml:space="preserve" ><g><path d="M386.689 304.403c-35.587 0-64.538 28.951-64.538 64.538s28.951 64.538 64.538 64.538c35.593 0 64.538-28.951 64.538-64.538s-28.951-64.538-64.538-64.538zm0 96.807c-17.796 0-32.269-14.473-32.269-32.269s14.473-32.269 32.269-32.269 32.269 14.473 32.269 32.269c0 17.797-14.473 32.269-32.269 32.269zM166.185 304.403c-35.587 0-64.538 28.951-64.538 64.538s28.951 64.538 64.538 64.538 64.538-28.951 64.538-64.538-28.951-64.538-64.538-64.538zm0 96.807c-17.796 0-32.269-14.473-32.269-32.269s14.473-32.269 32.269-32.269c17.791 0 32.269 14.473 32.269 32.269 0 17.797-14.473 32.269-32.269 32.269zM430.15 119.675a16.143 16.143 0 0 0-14.419-8.885h-84.975v32.269h75.025l43.934 87.384 28.838-14.5-48.403-96.268z"  opacity="1" data-original="#000000"></path><path d="M216.202 353.345h122.084v32.269H216.202zM117.781 353.345H61.849c-8.912 0-16.134 7.223-16.134 16.134 0 8.912 7.223 16.134 16.134 16.134h55.933c8.912 0 16.134-7.223 16.134-16.134 0-8.912-7.223-16.134-16.135-16.134zM508.612 254.709l-31.736-40.874a16.112 16.112 0 0 0-12.741-6.239H346.891V94.655c0-8.912-7.223-16.134-16.134-16.134H61.849c-8.912 0-16.134 7.223-16.134 16.134s7.223 16.134 16.134 16.134h252.773V223.73c0 8.912 7.223 16.134 16.134 16.134h125.478l23.497 30.268v83.211h-44.639c-8.912 0-16.134 7.223-16.134 16.134 0 8.912 7.223 16.134 16.134 16.134h60.773c8.912 0 16.134-7.223 16.135-16.134V264.605c0-3.582-1.194-7.067-3.388-9.896zM116.706 271.597H42.487c-8.912 0-16.134 7.223-16.134 16.134 0 8.912 7.223 16.134 16.134 16.134h74.218c8.912 0 16.134-7.223 16.134-16.134.001-8.911-7.222-16.134-16.133-16.134zM153.815 208.134H16.134C7.223 208.134 0 215.357 0 224.269s7.223 16.134 16.134 16.134h137.681c8.912 0 16.134-7.223 16.134-16.134s-7.222-16.135-16.134-16.135z"  opacity="1" data-original="#000000"></path><path d="M180.168 144.672H42.487c-8.912 0-16.134 7.223-16.134 16.134 0 8.912 7.223 16.134 16.134 16.134h137.681c8.912 0 16.134-7.223 16.134-16.134.001-8.911-7.222-16.134-16.134-16.134z"  opacity="1" data-original="#000000"></path></g></svg>
                                                {{translate("Shipping Info")}}
                                            </button>
                                    </li>

                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link p-3 wave-btn" id="pills-payment-tab"
                                            data-bs-toggle="pill" data-bs-target="#pills-payment"
                                            type="button" role="tab" aria-controls="pills-payment"
                                            aria-selected="false">
                                            <svg  version="1.1"   x="0" y="0" viewBox="0 0 100 100" xml:space="preserve" ><g><path d="M86 70.5v-39c0-3.58-2.92-6.5-6.5-6.5h-59c-3.58 0-6.5 2.92-6.5 6.5v39c0 3.58 2.92 6.5 6.5 6.5h59c3.58 0 6.5-2.92 6.5-6.5zm-4 0a2.5 2.5 0 0 1-2.5 2.5h-59a2.5 2.5 0 0 1-2.5-2.5V40h64zM82 36H18v-4.5a2.5 2.5 0 0 1 2.5-2.5h59a2.5 2.5 0 0 1 2.5 2.5z"  opacity="1" data-original="#000000" ></path><path d="M70.5 66c3.58 0 6.5-2.92 6.5-6.5S74.08 53 70.5 53 64 55.92 64 59.5s2.92 6.5 6.5 6.5zm0-9a2.5 2.5 0 0 1 0 5 2.5 2.5 0 0 1 0-5zM24 53h30c1.1 0 2-.9 2-2s-.9-2-2-2H24c-1.1 0-2 .9-2 2s.9 2 2 2zM24 63h23c1.1 0 2-.9 2-2s-.9-2-2-2H24c-1.1 0-2 .9-2 2s.9 2 2 2z"  opacity="1" data-original="#000000" ></path></g></svg>
                                                {{translate("Payment Info")}}
                                            </button>
                                    </li>


                                </ul>
                            </div>

                            <div class="tab-content checkout-form-content">
                                <div class="tab-pane fade show active" id="pills-bill-info" role="tabpanel"
                                    aria-labelledby="pills-bill-info-tab">
                                    <div class="tab-header">
                                        <h5>
                                            {{translate("Billing Information")}}
                                        </h5>

                                        <p class="text-muted">
                                            {{translate("Please fill all information below")}}
                                        </p>

                                    </div>

                                    <div>

                                        @if(auth()->user())

                                            <div class="shipping-address">
                                                <div class="row g-4">
                                                    @if(auth()->user()->billing_address)

                                                        @foreach(auth()->user()->billing_address as $key => $address)

                                                            <div class="col-xl-6">
                                                                <div class="address-card">
                                                                    <div class="form-check card-radio">
                                                                        <input id="{{$loop->index}}-address" type="radio" class="form-check-input checkout-radio-btn" value="{{$key}}" name="address_key" {{$loop->index == 0 ? 'checked' :""}}>

                                                                        <label class="form-check-label pointer" for="{{$loop->index}}-address">

                                                                            <span class="text-wrap d-flex flex-column gap-1">
                                                                                <span class="address-title">
                                                                                    {{k2t($key)}}
                                                                                </span>

                                                                              @foreach ( $address as $addresKey => $val )

                                                                                <span class="address-item">
                                                                                    <span>
                                                                                        {{k2t($addresKey)}}
                                                                                    </span>
                                                                                    <small>
                                                                                        {{$val}}
                                                                                    </small>
                                                                                </span>

                                                                              @endforeach

                                                                            </span>

                                                                        </label>
                                                                    </div>

                                                                    <div class="address-actions">
                                                                        <a href="{{route('user.address.delete',$key)}}"  class="delete-address address-action-btn danger" type="button">
                                                                            <i class="fas fa-trash-alt"></i>
                                                                        </a>

                                                                        <a href="javascript:void(0)" data-key="{{$key}}" data-address = "{{ collect($address) }}" class="edit-address address-action-btn " type="button">
                                                                            <i class="fa-regular fa-pen-to-square"></i>
                                                                        </a>
                                                                    </div>

                                                                </div>
                                                            </div>

                                                        @endforeach

                                                    @endif

                                                    <div class="col-12">
                                                        <button type="button" class="create-address" data-bs-toggle="modal" data-bs-target="#createAddress">
                                                            <span class="address-icon">
                                                                <svg id="fi_2312340" enable-background="new 0 0 24 24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                    <path d="m14.25 0h-11.5c-1.52 0-2.75 1.23-2.75 2.75v15.5c0 1.52 1.23 2.75 2.75 2.75h6.59c-.54-1.14-.84-2.41-.84-3.75 0-1.15.22-2.25.64-3.26.2-.51.45-1 .74-1.45.65-1.01 1.49-1.87 2.48-2.54.51-.35 1.05-.64 1.63-.86.93-.39 1.95-.61 3.01-.63v-5.76c0-1.52-1.23-2.75-2.75-2.75z" fill="#eceff1"></path><g fill="#90a4ae"><path d="m14 9c0 .05 0 .1-.01.14-.58.22-1.12.51-1.63.86h-8.36c-.55 0-1-.45-1-1s.45-1 1-1h9c.55 0 1 .45 1 1z"></path><path d="m9.88 12.54c-.29.45-.54.94-.74 1.45-.04.01-.09.01-.14.01h-5c-.55 0-1-.45-1-1s.45-1 1-1h5c.38 0 .72.22.88.54z"></path><path d="m8 6h-4c-.552 0-1-.448-1-1s.448-1 1-1h4c.552 0 1 .448 1 1s-.448 1-1 1z"></path></g><path d="m17.25 24c-3.722 0-6.75-3.028-6.75-6.75s3.028-6.75 6.75-6.75 6.75 3.028 6.75 6.75-3.028 6.75-6.75 6.75z" class="added"></path><path d="m17.25 21c-.552 0-1-.448-1-1v-5.5c0-.552.448-1 1-1s1 .448 1 1v5.5c0 .552-.448 1-1 1z" fill="#fff"></path><path d="m20 18.25h-5.5c-.552 0-1-.448-1-1s.448-1 1-1h5.5c.552 0 1 .448 1 1s-.448 1-1 1z" fill="#fff"></path></svg>
                                                            </span>
                                                            {{translate('Add New Billing Address')}}
                                                        </button>
                                                    </div>

                                                </div>
                                            </div>

                                        @else

                                            <div class="row g-4">
                                                <div class="col-md-6">
                                                    <div>
                                                        <label for="billinginfo-firstName"
                                                            class="form-label">
                                                            {{translate("First
                                                            Name")}} <span class="text-danger">*</span>
                                                        </label>

                                                        <input type="text" class="form-control user-info"
                                                            id="billinginfo-firstName"
                                                            name="first_name"
                                                            placeholder="{{translate('Enter first name')}}" value="{{old('first_name') ?  old('first_name') : @$user->name }}">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div>
                                                        <label for="billinginfo-lastName"
                                                            class="form-label">
                                                            {{translate("Last
                                                            Name")}}    <span class="text-danger">*</span>
                                                            </label>
                                                        <input type="text" class="form-control user-info"
                                                            id="billinginfo-lastName"
                                                            name="last_name"
                                                            placeholder="{{translate('Enter last name')}}" value="{{old('phone')?old('phone') :@$user->last_name}}">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div>
                                                        <label for="billinginfo-email" class="form-label">
                                                            {{translate("Email")}}
                                                            <span class="text-danger">*</span></label>
                                                        <input type="email" name="email" class="form-control user-info"
                                                            id="billinginfo-email"  value="{{@$user->email ? $user->email : old('email')}}" placeholder="Enter email">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div>
                                                        <label for="billinginfo-phone"
                                                            class="form-label">
                                                                {{translate("Phone")}}
                                                            <span class="text-danger">*</span>
                                                            </label>
                                                        <input type="text" name="phone" class="form-control user-info"
                                                            id="billinginfo-phone"
                                                            value="{{old('phone')?old('phone') :@$user->phone}}"
                                                            placeholder="Enter phone no.">
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div>
                                                        <label for="billinginfo-address"
                                                            class="form-label">
                                                            {{translate("Address")}}

                                                            <span class="text-danger"> *</span>

                                                            </label>
                                                        <textarea name="address" class="form-control user-info" id="billinginfo-address"
                                                            placeholder="Enter address" rows="3">{{old('address')? old('address') : @$user->address->address }}</textarea>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div>
                                                        <label for="zip" class="form-label">
                                                            {{translate('Zip Code')}} <span class="text-danger" >*</span>
                                                        </label>
                                                        <input  class="form-control user-info" type="text" id="zip" name="zip" value="{{old('zip') ? old('zip') :  @$user->address->zip  }}"
                                                        placeholder="{{translate('1205')}}" required>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div>
                                                        <label for="city" class="form-label">
                                                            {{translate('City')}} <span class="text-danger" >*</span>
                                                        </label>
                                                        <input  class="form-control user-info" type="text" id="city" name="city" value="{{old('city') ? old('city') :  @$user->address->city  }}"
                                                        placeholder="{{translate('Enter City')}}" required>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div>
                                                        <label for="state" class="form-label">
                                                            {{translate('State')}} <span class="text-danger" >*</span>
                                                        </label>
                                                        <input  class="form-control user-info" type="text" id="state" name="state" value="{{old('state') ? old('state') :  @$user->address->state  }}"
                                                        placeholder="{{translate('Enter State')}}" required>
                                                    </div>
                                                </div>
                                            </div>

                                        @endif

                                        <div class="d-flex align-items-center @if(!auth()->user()) justify-content-sm-between justify-content-start @else justify-content-end  @endif flex-wrap gap-4 mt-5">

                                            @if(!auth()->user())
                                                <div class="d-flex align-items-center gap-2">

                                                    <input type="checkbox" name="create_account" value="1" class="custom-checkbox" id="create_account">
                                                    <label for="create_account">
                                                        {{translate("Register with the provided information")}}
                                                    </label>

                                                </div>
                                            @endif

                                            <button type="button"
                                                class="nexttab check-input btn-label  wave-btn"
                                                data-nexttab="pills-bill-address-tab">
                                                    <i class="fa-solid fa-truck-arrow-right label-icon align-middle fs-14"></i>
                                                    {{translate(" Proceed
                                                    to Shipping")}}
                                            </button>
                                        </div>

                                    </div>

                                </div>

                                <div class="tab-pane fade" id="pills-bill-address" role="tabpanel"
                                    aria-labelledby="pills-bill-address-tab">

                                    <div class="tab-header">
                                        <h5>
                                            {{translate("Shipping Method")}}
                                        </h5>
                                        <p class="text-muted">
                                            {{translate("Please fill all information below")}}
                                        </p>
                                    </div>

                                    <div class="mt-4">
                                        <div class="row g-4">
                                                @foreach($shippingDeliverys as $shippingDelivery)
                                                    <div class="col-md-6">
                                                        <div class="form-check card-radio">
                                                            <input   data-shipping_price="{{short_amount($shippingDelivery->price)}}" id="{{$shippingDelivery->id}}" name="shipping_method"
                                                                type="radio" class="form-check-input shiping-info checkout-radio-btn" value="{{$shippingDelivery->id}}">
                                                            <label class="form-check-label pointer"
                                                                for="{{$shippingDelivery->id}}">
                                                                <span
                                                                    class="fs-16 float-end mt-3 text-wrap d-block">{{show_currency()}}{{short_amount($shippingDelivery->price)}}
                                                                </span>

                                                                <span class="fs-14 mb-1 text-wrap d-block">
                                                                    {{@$shippingDelivery->method->name}}
                                                                </span>

                                                                <span
                                                                    class="text-muted fs-12 fw-normal text-wrap d-block">
                                                                    {{translate("Delivery In")}}
                                                                    {{$shippingDelivery->duration}}  {{translate('Days')}}
                                                                 </span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-start justify-content-sm-between justify-content-center flex-wrap gap-4 mt-5">
                                        <button type="button" class="btn-label previestab"
                                            data-previous="pills-bill-info-tab"><i
                                                class="fa-solid fa-arrow-left label-icon align-middle fs-14"></i>
                                                {{translate("Back
                                                to Personal Info")}}

                                        </button>
                                        <button type="button"
                                        class="btn-label nexttab shiping-input"
                                        data-nexttab="pills-payment-tab"><i
                                            class="fa-solid fa-credit-card label-icon align-middle fs-14"></i>
                                        {{translate("Continue
                                        to Payment")}}
                                        </button>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="pills-payment" role="tabpanel"
                                    aria-labelledby="pills-payment-tab">

                                    @if(count($paymentOptions) == 0 || in_array(0,$paymentOptions))
                                        <div class="mb-5">


                                            <div class="row">

                                                    <div class="col-lg-12">
                                                        <div class="form-check card-radio">
                                                            <input id="cod" type="radio" name="payment_type" value="1"
                                                                class="form-check-input">
                                                            <label class="form-check-label" for="cod">
                                                                <span class="d-flex align-items-center gap-4">
                                                                    <span class="payment_icon">
                                                                        <img src="{{asset('assets/images/frontend/payment/cod.jpg')}}" alt="cod.jpg">
                                                                    </span>

                                                                    <span class="fs-14 text-wrap">
                                                                        {{translate("Cash on
                                                                        Delivery")}}
                                                                    </span>
                                                                </span>
                                                            </label>
                                                        </div>
                                                    </div>

                                            </div>
                                        </div>
                                    @endif

                                    <div>
                                        <div class="tab-header">
                                            <h5>
                                                {{translate("Payment Selection")}}
                                            </h5>

                                            <p class="text-muted">
                                                {{translate("Please select A Payment Method")}}
                                            </p>
                                        </div>

                                        <div class="row g-4">
                                            @foreach($paymentMethods as $paymentMethod)
                                                @if(count($paymentOptions) == 0 || in_array($paymentMethod->id,$paymentOptions))
                                                    <div class="col-xl-4 col-md-6">
                                                        <div class="form-check card-radio">
                                                            <input type="radio" id="payment-{{$paymentMethod->id}}" name="payment_type" value="{{$paymentMethod->unique_code}}" class="form-check-input payment-radio-btn">
                                                            <label class="form-check-label pointer" for="payment-{{$paymentMethod->id}}">
                                                                <span class="d-flex align-items-center gap-4">
                                                                    <span class="payment_icon">
                                                                            <img src="{{ show_image(file_path()['payment_method']['path'].'/'.$paymentMethod->image,file_path()['payment_method']['size'])}}" alt="{{$paymentMethod->image}}">
                                                                    </span>

                                                                    <span class="fs-14 text-wrap">
                                                                        {{$paymentMethod->name}}
                                                                    </span>
                                                                </span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach

                                        </div>
                                    </div>

                                    <div class="d-flex align-items-start justify-content-sm-between justify-content-center flex-wrap gap-4 mt-5">
                                        <button type="button" class="btn-label previestab"
                                            data-previous="pills-bill-address-tab"><i
                                                class="fa-solid fa-arrow-left label-icon align-middle fs-14"></i>
                                                {{translate("Back
                                                to Shipping")}}
                                            </button>
                                        <button type="submit"
                                            class="nexttab check-input btn-label  wave-btn oder-btn"
                                            ><i class="fa-solid fa-cart-shopping label-icon align-middle fs-14 ">

                                            </i>

                                                {{translate("Order")}}
                                            </button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-3 col-lg-4">
                    <div class="card checkout-product">
                        <div class="card-header">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <h5 class="card-title fs-18 mb-0">
                                        {{translate("Order Summary")}}
                                    </h5>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive table-card">
                                <table class="table table-borderless align-middle mb-0 w-100">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" class="fs-14">
                                                {{translate('Product')}}
                                            </th>

                                            <th scope="col" class="text-end fs-14 nowrap">
                                                {{translate("Price")}}
                                            </th>
                                        </tr>
                                    </thead>
                                    @php

                                        $subTotal =  0;

                                    @endphp
                                    <tbody>
                                        @foreach($items as $data)
                                            <tr>
                                                <td class="text-start">
                                                  <div class="d-flex align-items-start gap-4 nowrap">
                                                        <div class="checkout-pro-img m-0">
                                                            <img class="m-0" src="{{show_image(file_path()['product']['featured']['path'].'/'.$data->product->featured_image,file_path()['product']['featured']['size'])}}" alt="{{$data->product->name}}">
                                                        </div>
                                                        <div class="check-item">
                                                            <h4 class="product-title pb-1">
                                                                <a href="{{route('product.details',[make_slug($data->product->name),$data->product->id])}}">
                                                                {{limit_words($data->product->name,2)}}
                                                                </a>
                                                            </h4>
                                                            <p class="text-muted fs-12 lh-1">{{show_currency()}}{{short_amount($data->price)}} x {{$data->quantity}}  ({{$data->attributes_value}}) </p>
                                                        </div>
                                                  </div>
                                                </td>
                                                <td class="text-end nowrap">{{show_currency()}}{{short_amount($data->total)}}</td>
                                            </tr>
                                            @php
                                              $subTotal += ($data->total);
                                            @endphp
                                        @endforeach
                                    </tbody>
                                </table>
                                @php
                                    $subTotal = short_amount($subTotal);
                                @endphp


                            </div>
                            <ul>

                                @if(auth()->user())
                                    <li class="py-4 coupon-input">
                                        <div class="input-group">
                                            <input name="coupon_code" type="text" class="form-control border-2" placeholder="Enter your Coupon" aria-label="Enter your Coupon">
                                            <button type="button" class="input-group-text btn-success fs-14 apply-btn " >
                                                {{translate("Apply")}}
                                            </button>
                                        </div>
                                    </li>
                                @endif

                                <li class="d-flex align-items-center justify-content-between gap-4 subtotal">
                                    <span class="fw-semibold ps-4 py-4  fs-14 nowrap">
                                        {{translate("Sub Total")}}:</span>
                                    <span class="fw-semibold text-end pe-4 py-3  fs-14 nowrap" data-sub ="{{$subTotal}}" id="subtotalamount" >{{show_currency()}}{{$subTotal}}</span>
                                </li>

                                <li class="order-coupon-item d-flex align-items-center justify-content-between gap-4 @if(!session()->has('coupon')) d-none @endif">
                                    <span  class="ps-4 py-2 nowrap fs-14">
                                        {{translate("Discount")}}
                                        <span class="text-muted">({{translate("Coupon")}})</span>
                                        : </span>
                                    <span class="text-end pe-4 py-2 nowrap fs-14">  <span>- {{show_currency()}}<span
                                        id="couponamount">{{@session()->get('coupon')['amount']}}</span></span></span>
                                </li>

                                <li class="order-cost-item order-shipping-cost d-none d-flex align-items-center justify-content-between gap-4">
                                    <span class="ps-4 py-3 nowrap fs-14">{{translate("Shipping Charge")}} :</span>
                                    <span class="text-end pe-4 py-3 nowrap fs-14" >
                                        {{show_currency()}}<span id="shipping_cost">0</span>
                                    </span>
                                </li>

                                <li class="table-active d-flex align-items-center justify-content-between gap-4">
                                    <h6 class="ps-4 py-3 nowrap fs-14 fw-bold">{{translate("Total")}} :</h6>
                                    <span class="text-end pe-4 py-3 nowrap fs-14">
                                       <span id="totalamount" class="fw-bold">
                                          {{show_currency()}}{{$subTotal - @session()->get('coupon')['amount']}}
                                        </span>
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<div class="modal fade" id="createAddress" tabindex="-1" data-bs-backdrop="static" aria-labelledby="createAddress" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" >
                   {{translate("Create Billing Address")}}
                </h5>

                <button type="button" class="btn btn-danger fs-14 modal-closer rounded-circle" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{route("user.address.store")}}" method="post" >
                @csrf

                <div class="modal-body">
                    <div class="row g-4">

                        <div class="col-md-12">
                            <div>
                                <label for="address_name"
                                    class="form-label">
                                    {{translate("Address Name")}} <span class="text-danger">*</span>
                                </label>

                                <input type="text" class="form-control"
                                    id="address_name"
                                    name="address_name"
                                    placeholder="{{translate('Enter Name')}}" value="{{old('address_name') }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div>
                                <label for="billinginfo-firstName"
                                    class="form-label">
                                    {{translate("First
                                    Name")}} <span class="text-danger">*</span>
                                </label>

                                <input type="text" class="form-control "
                                    id="billinginfo-firstName"
                                    name="first_name"
                                    placeholder="{{translate('Enter first name')}}" value="{{old('first_name') ?  old('first_name') : @$user->name }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div>
                                <label for="billinginfo-lastName"
                                    class="form-label">
                                    {{translate("Last
                                    Name")}}    <span class="text-danger">*</span>
                                    </label>
                                <input type="text" class="form-control "
                                    id="billinginfo-lastName"
                                    name="last_name"
                                    placeholder="{{translate('Enter last name')}}" value="{{old('phone')?old('phone') :@$user->last_name}}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div>
                                <label for="billinginfo-email" class="form-label">
                                    {{translate("Email")}}
                                    <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control"
                                    id="billinginfo-email"  value="{{@$user->email ? $user->email : old('email')}}" placeholder="Enter email">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div>
                                <label for="billinginfo-phone"
                                    class="form-label">
                                        {{translate("Phone")}}
                                    <span class="text-danger">*</span>
                                    </label>
                                <input type="text" name="phone" class="form-control "
                                    id="billinginfo-phone"
                                    value="{{old('phone')?old('phone') :@$user->phone}}"
                                    placeholder="Enter phone no.">
                            </div>
                        </div>

                        <div class="col-12">
                            <div>
                                <label for="billinginfo-address"
                                    class="form-label">
                                    {{translate("Address")}}

                                    <span class="text-danger"> *</span>

                                    </label>
                                <textarea name="address" class="form-control " id="billinginfo-address"
                                    placeholder="Enter address" rows="3">{{old('address')? old('address') : @$user->address->address }}</textarea>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div>
                                <label for="zip" class="form-label">
                                    {{translate('Zip Code')}} <span class="text-danger" >*</span>
                                </label>
                                <input  class="form-control " type="text" id="zip" name="zip" value="{{old('zip') ? old('zip') :  @$user->address->zip  }}"
                                placeholder="{{translate('1205')}}" required>
                            </div>
                        </div>



                        <div class="col-md-4">
                            <div>
                                <label for="city" class="form-label">
                                    {{translate('City')}} <span class="text-danger" >*</span>
                                </label>
                                <input  class="form-control " type="text" id="city" name="city" value="{{old('city') ? old('city') :  @$user->address->city  }}"
                                placeholder="{{translate('Enter City')}}" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div>
                                <label for="state" class="form-label">
                                    {{translate('State')}} <span class="text-danger" >*</span>
                                </label>
                                <input  class="form-control" type="text" id="state" name="state" value="{{old('state') ? old('state') :  @$user->address->state  }}"
                                placeholder="{{translate('Enter State')}}" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="d-flex align-items-center gap-4">
                        <button type="button" class="btn btn-danger fs-12 px-3" data-bs-dismiss="modal">
                            {{translate("Cancel")}}
                        </button>
                        <button type="submit" class="btn btn-success fs-12 px-3">
                            {{translate('Submit')}}
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="updateAddress" tabindex="-1" data-bs-backdrop="static" aria-labelledby="updateAddress" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" >
                   {{translate("Update Billing Address")}}
                </h5>

                <button type="button" class="btn btn-danger fs-14 modal-closer rounded-circle" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{route("user.address.update")}}" method="post" >
                @csrf

                <div class="modal-body">
                    <div class="row g-4 address-section">


                    </div>
                </div>

                <div class="modal-footer">
                    <div class="d-flex align-items-center gap-4">
                        <button type="button" class="btn btn-danger fs-12 px-3" data-bs-dismiss="modal">
                            {{translate("Cancel")}}
                        </button>
                        <button type="submit" class="btn btn-success fs-12 px-3">
                            {{translate('Submit')}}
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

@endsection


@push('scriptpush')

<script>


        "use strict";

        $(document).on('click',".edit-address",function(e){


            var key = $(this).attr('data-key');

            var html =  `<div class="col-md-12">

                           <input type="hidden" class="form-control"
                                    id="address_key"
                                    name="address_key"
                                    value="${ key }">

                            <div>
                                <label for="address_name"
                                    class="form-label">
                                    {{translate("Address Name")}} <span class="text-danger">*</span>
                                </label>

                                <input type="text" class="form-control"
                                    id="address_name"
                                    name="address_name"
                                    placeholder="{{translate('Enter Name')}}" value="${ key.replace(/_/g, ' ') }">
                            </div>
                        </div>`

            var address = $(this).attr('data-address');
            address = JSON.parse(address);

            for(var i in  address){
                html+=`<div class="col-md-6">
                            <div>
                                <label for="billinginfo-firstName"
                                    class="form-label">
                                    ${i.replace(/_/g, ' ')} <span class="text-danger">*</span>
                                </label>

                                <input type="text" class="form-control "
                                    id="billinginfo-firstName"
                                    name="${i}"
                                    value="${address[i]}">
                            </div>
                        </div>`;
            }

            $('.address-section').html(html)
            var modal = $("#updateAddress");
            modal.modal('show');

            e.preventDefault();
        })


</script>
@endpush
