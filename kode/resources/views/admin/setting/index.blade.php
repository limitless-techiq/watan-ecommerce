@extends('admin.layouts.app')
@push('style-include')
    <link rel="stylesheet" href="{{asset('assets/backend/css/spectrum.css') }}">
@endpush
@section('main_content')
<div class="page-content">
	<div class="container-fluid">

        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">
                {{translate($title)}}
            </h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">
                        {{translate('Home')}}
                    </a></li>
                    <li class="breadcrumb-item active">
                        {{translate('System Setting')}}
                    </li>
                </ol>
            </div>
        </div>

		<div class="card">
			<div class="card-header border-bottom-dashed">
				<div class="d-flex align-items-center">
					<h5 class="card-title mb-0 flex-grow-1">
						{{translate('System Setting')}}
					</h5>
				</div>
			</div>

			<div class="card-body">
                <form class="d-flex flex-column gap-4" action="{{route('admin.general.setting.store')}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="border rounded p-3">
                                <h6 class="mb-3">
                                    {{translate('Seller Panel')}} <span class="text-danger" >*</span>
                                </h6>
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="mb-2 d-flex align-items-center">
                                            <div class="form-check form-switch d-flex align-items-center justify-content-end">
                                                <input id="seller-mode" class="form-check-input" type="checkbox"  {{ $general->seller_mode == 'active' ?"checked" :'' }}     >
                                            </div>
                                            <span id="seller-status" class="ms-3">
                                                {{  ucfirst($general->seller_mode) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="border rounded p-3">
                                <h6 class="mb-3">
                                    {{translate('Debug Mode')}} <span class="text-danger" >*</span>
                                </h6>
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="mb-2 d-flex align-items-center">
                                            <div class="form-check form-switch d-flex align-items-center justify-content-end">
                                                <input id="debug-mode" data-value="{{ env('APP_DEBUG') ? 'false' :'true' }} " class="form-check-input" type="checkbox" {{ env('APP_DEBUG') ?"checked" :'' }}     >
                                            </div>
                                            <span id="debug-status" class="ms-3">
                                                {{  env('APP_DEBUG') ? 'Active' :'Inactive' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded p-3">
                            <h6 class="mb-3">
                            {{translate('Site Information')}} <span class="text-danger" >*</span>
                            </h6>
                            <div class="row g-3">
                            <div class="col-lg-6">
                                <label for="site_name" class="form-label">
                                    {{translate('Site Name')}} <span class="text-danger" >*</span>
                                    </label>
                                <input type="text" class="form-control"  name="site_name" id="site_name" value="{{$general->site_name}}" >
                            </div>

                            <div class="col-lg-6">
                                <div>
                                    <label for="copyright_text" class="form-label">
                                        {{translate('Copyright Text')}} <span class="text-danger" >*</span>
                                    </label>
                                    <textarea class="form-control" name="copyright_text" id="copyright_text" cols="30" rows="1">{{$general->copyright_text}}</textarea>
                                </div>
                            </div>


                            <div class="col-lg-6">
                                <label for="mail_from" class="form-label">
                                    {{translate('Email Address')}}
                                    </label>
                                <input type="email" class="form-control"  name="mail_from" id="mail_from" value="{{$general->mail_from}}" placeholder="example@gmail.com">
                            </div>

                            <div class="col-lg-6">
                                <label for="phone" class="form-label">
                                    {{translate('Phone')}}
                                    </label>
                                <input type="text" class="form-control"  name="phone" id="phone" value="{{$general->phone}}" placeholder="example@gmail.com">
                            </div>

                            <div class="col-lg-6">
                                <div>
                                    <label for="address" class="form-label">
                                        {{translate('Address')}}
                                    </label>
                                    <textarea class="form-control" name="address" id="address" cols="30" rows="1">{{$general->address}}</textarea>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div>
                                    <label for="order_prefix" class="form-label">
                                        {{translate('Order Prefix')}} <span class="text-danger" >*</span>
                                    </label>
                                        <input type="text" class="form-control" name="order_prefix" value="{{$general->order_prefix}}" id="order_prefix">
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <label for="primary_color" class="form-label">
                                    {{translate('Primary Color')}} <span class="text-danger" >*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text" >
                                        <input  type='text' class="color_picker_show"  value="{{$general->primary_color}}">
                                    </span>
                                    <input type="text" class="form-control color_code" id="primary_color" name="primary_color" value="{{$general->primary_color}}">
                                    <span id="reset-primary-color" class="input-group-text pointer"><i class="las la-redo-alt"></i></span>
                                </div>
                            </div>

                                <div class="col-lg-4">
                                <label for="secondary_color " class="form-label">
                                    {{translate('Secondary Color')}} <span class="text-danger" >*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text" >
                                        <input type='text' class="color_picker_show" value="{{$general->secondary_color}}"/>
                                    </span>
                                    <input type="text" class="form-control color_code" id="secondary_color" name="secondary_color" value="{{$general->secondary_color}}">
                                    <span id="reset-secondary-color" class="input-group-text pointer"><i class="las la-redo-alt"></i></span>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <label for="font_color" class="form-label">
                                    {{translate('Font Color')}} <span class="text-danger" >*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text" >
                                        <input type='text' class="color_picker_show"  value="{{$general->font_color}}"/>
                                    </span>
                                    <input type="text" class="form-control color_code" id="font_color" name="font_color" value="{{$general->font_color}}">
                                    <span id="reset-font-color" class="input-group-text pointer"><i class="las la-redo-alt"></i></span>
                                </div>
                            </div>

                            </div>
                    </div>

                    <div class="border rounded p-3">
                            <h6 class="mb-3">
                                 {{translate('Currency & Percentage')}}
                            </h6>
                            <div class="row g-3">

                                <div class="col-lg-4">
                                    <label for="currency_name" class="form-label">
                                        {{translate('Currency')}} <span class="text-danger" >*</span>
                                    </label>
                                    <input type="text" name="currency_name" id="currency_name" class="form-control" value="{{$general->currency_name}}" required placeholder="Name">
                                </div>

                                <div class="col-lg-4">
                                    <div>
                                        <label for="currency_symbol" class="form-label">
                                                {{translate('Currency Symbol')}}
                                        </label> <span class="text-danger" >*</span>
                                        <input type="text" name="currency_symbol" id="currency_symbol" class="form-control" value="{{$general->currency_symbol}}" required placeholder="Currency Symbol">
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <label for="commission" class="form-label">
                                        {{translate('Commission On Sale')}} <span class="text-danger" >*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="commission" name="commission" value="{{round_number($general->commission)}}">
                                        <span class="input-group-text" >%</span>
                                    </div>
                                </div>

                            </div>
                    </div>

                    <div class="border rounded p-3">
                        <h6 class="mb-3">
                            {{translate('Configurations')}}
                        </h6>
                            <div class="row g-3">

                                <div class="col-lg-4">
                                    <label for="f_preloader" class="form-label">
                                        {{translate('Frontend Preloader')}} <span class="text-danger" >*</span>
                                    </label>

                                    <select class="form-select" id="f_preloader" name="preloader">
                                        <option value="1" @if(@$general->preloader == 1) selected @endif>
                                            {{translate('Enable')}}</option>
                                        <option value="0" @if(@$general->preloader == 0) selected @endif>
                                            {{translate('Disable')}}
                                        </option>
                                    </select>
                                </div>

                                <div class="col-lg-4">
                                    <label for="guest_checkout" class="form-label">
                                        {{translate('Guest Checkout')}} <span class="text-danger" >*</span>
                                    </label>
                                    <select class="form-select" id="guest_checkout" name="guest_checkout">
                                        <option value="1" @if(@$general->guest_checkout == 1) selected @endif>{{translate('Enable')}}</option>
                                        <option value="0" @if(@$general->guest_checkout == 0) selected @endif>{{translate('Disable')}}</option>
                                    </select>
                                </div>

                                <div class="col-lg-4">
                                    <label for="maintenance_mode" class="form-label">
                                        {{translate('Maintenance Mode')}} <span class="text-danger" >*</span>
                                    </label>
                                    <select class="form-select" id="maintenance_mode" name="maintenance_mode">
                                        <option value="1" @if(@$general->maintenance_mode == 1) selected @endif>{{translate('Enable')}}</option>
                                        <option value="2" @if(@$general->maintenance_mode == 2) selected @endif>{{translate('Disable')}}</option>
                                    </select>
                                </div>

                                <div class="col-lg-4">
                                    <label for="strong_password" class="form-label">
                                        {{translate('Strong Password')}} <span class="text-danger" >*</span>
                                    </label>
                                    <select class="form-select" id="strong_password" name="strong_password">
                                        <option value="1" @if(@$general->strong_password == 1) selected @endif>{{translate('Enable')}}</option>
                                        <option value="2" @if(@$general->strong_password == 2) selected @endif>{{translate('Disable')}}</option>
                                    </select>
                                </div>

                                <div class="col-lg-4">
                                    <label for="email_notification" class="form-label">
                                        {{translate('Email Notification')}} <span class="text-danger" >*</span>
                                    </label>
                                    <select class="form-select" id="email_notification" name="email_notification">
                                        <option value="1" @if($general->email_notification == 1) selected @endif>{{translate('Enable')}}</option>
                                        <option value="2" @if($general->email_notification == 2) selected @endif>{{translate('Disable')}}</option>
                                    </select>
                                </div>

                                <div class="col-lg-4">
                                    <label for="seller_reg_allow" class="form-label">
                                            {{translate('Seller Registration Status')}} <span class="text-danger" >*</span>
                                    </label>
                                    <select class="form-select" id="seller_reg_allow" name="seller_reg_allow">
                                        <option value="1" @if($general->seller_reg_allow == 1) selected @endif>{{translate('Enable')}}</option>
                                        <option value="2" @if($general->seller_reg_allow == 2) selected @endif>{{translate('Disable')}}</option>
                                    </select>
                                </div>

                                <div class="col-lg-4">
                                    <label for="cod" class="form-label">
                                        {{translate('Cash On Delivery')}} <span class="text-danger" >*</span>
                                    </label>
                                    <select class="form-select" id="cod" name="cod">
                                        <option value="active" @if($general->cod == 'active') selected @endif>{{translate('Active')}}</option>
                                        <option value="inactive" @if($general->cod == 'inactive') selected @endif>{{translate('Inactive')}}</option>
                                    </select>
                                </div>
                            </div>
                    </div>


                    <div class="border rounded p-3">
                        <h6 class="mb-3">
                            {{translate('OTP & Login Configuration')}}
                        </h6>
                        <div class="row g-3">

                            @php
                               $otpConfiguration = @json_decode($general->otp_configuration);
                            @endphp

            
                            <div class="col-lg-4">
                                <label for="phone_otp" class="form-label">
                                    {{translate('OTP with mobile')}} <span class="text-danger" >*</span>
                                </label>

                                <select class="form-select" name="otp_configuration[phone_otp]" id="phone_otp">
                                    <option value="1" @if(@$otpConfiguration->phone_otp == 1) selected @endif>
                                        {{translate('Enable')}}</option>
                                    <option value="0" @if(@$otpConfiguration->phone_otp == 0) selected @endif>
                                        {{translate('Disable')}}
                                    </option>
                                </select>
                            </div>

                            <div class="col-lg-4">
                                <label for="email_otp" class="form-label">
                                    {{translate('OTP with email')}} <span class="text-danger" >*</span>
                                </label>

                                <select class="form-select" name="otp_configuration[email_otp]" id="email_otp">
                                    <option value="1" @if(@$otpConfiguration->email_otp == 1) selected @endif>
                                        {{translate('Enable')}}</option>
                                    <option value="0" @if(@$otpConfiguration->email_otp == 0) selected @endif>
                                        {{translate('Disable')}}
                                    </option>
                                </select>
                            </div>

                            <div class="col-lg-4">
                                <label for="login_with_password" class="form-label">
                                    {{translate('Login with password')}} <span class="text-danger" >*</span>
                                </label>

                                <select class="form-select" name="otp_configuration[login_with_password]" id="login_with_password">
                                    <option value="1" @if(@$otpConfiguration->login_with_password == 1) selected @endif>
                                        {{translate('Enable')}}</option>
                                    <option value="0" @if(@$otpConfiguration->login_with_password == 0) selected @endif>
                                        {{translate('Disable')}}
                                    </option>
                                </select>
                                
                            </div>
                          
                            
                        </div>
                    </div>

                    <div class="border rounded p-3">
                            <h6 class="mb-3">
                            {{translate('Filter')}}
                            </h6>
                            <div class="row g-3">
                                <div class="col-lg-6">
                                    <label for="search_min" class="form-label">
                                        {{translate('Price Range (Min)')}} <span class="text-danger" >*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="search_min" name="search_min" value="{{round_number($general->search_min)}}" placeholder="100">
                                        <span class="input-group-text" >{{$general->currency_name}}</span>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label for="search_max" class="form-label">
                                        {{translate('Price Range (Max)')}} <span class="text-danger" >*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="search_max" name="search_max" value="{{round_number($general->search_max)}}" placeholder="200">
                                        <span class="input-group-text" >{{$general->currency_name}}</span>
                                    </div>
                                </div>
                            </div>
                    </div>

                    <div class="border rounded p-3">
                            <h6 class="mb-3">
                                {{translate('Logo & Icon')}}
                            </h6>
                            <div class="row g-3">
                            <div class="col-xl-3 col-lg-4">
                                <label for="site_logo" class="form-label">
                                    {{translate("Site Logo")}} <span class="text-danger" >*</span>
                                </label>
                                <input type="file" name="site_logo" id="site_logo" class="form-control">

                                <div class="mt-2">
                                    <img src="{{ show_image('assets/images/backend/logoIcon/'.$general->site_logo,file_path()['site_logo']['size']) }}" alt="{{@$general->site_logo}}" class="logo-preview">
                                </div>
                            </div>



                            <div class="col-xl-3 col-lg-4">
                                <label for="admin_site_logo" class="form-label">
                                    {{translate('Admin Site Logo')}} <span class="text-danger" >*</span>
                                </label>
                                <input type="file" name="admin_site_logo" id="admin_site_logo" class="form-control">

                                <div class="mt-2 ">
                                    <img src="{{show_image('assets/images/backend/AdminLogoIcon/'.$general->admin_logo_lg ,file_path()['admin_site_logo']['size'])}}" alt="{{$general->admin_logo_lg}}" class="bg-dark logo-preview">
                                </div>

                            </div>

                            <div class="col-xl-3 col-lg-4">
                                <label for="admin_site_logo_sm" class="form-label">
                                    {{translate('Admin Logo Icon')}} <span class="text-danger" >*</span>
                                </label>
                                <input type="file" name="admin_site_logo_sm" id="admin_site_logo_sm" class="form-control">

                                <div class="mt-2 ">
                                    <img src="{{show_image('assets/images/backend/AdminLogoIcon/'.$general->admin_logo_sm,file_path()['loder_logo']['size'])}}" alt="{{@$general->admin_logo_sm}}" class="icon-preview">
                                </div>

                            </div>

                            <div class="col-xl-3 col-lg-4">
                                <label for="site_favicon" class="form-label">
                                    {{translate('Site Favicon')}} <span class="text-danger" >*</span>
                                </label>
                                <input type="file" name="site_favicon" id="site_favicon" class="form-control">
                                <div class="fav-preview-image mt-2">
                                    <img src="{{ show_image('assets/images/backend/logoIcon/'.$general->site_favicon ,file_path()['favicon']['size']) }}" alt="{{$general->site_favicon}}" class="icon-preview">
                                </div>
                            </div>

                                @foreach(json_decode($general->invoice_logo) as $key => $value)
                                    <div class="col-xl-3 col-lg-4">
                                        <label for="{{$key}}" class="form-label">{{(ucfirst($key))}} <span class="text-danger" >*</span></label>
                                        <input type="file" name="invoice_logo[{{ $key }}]" id="{{$key}}" class="form-control">
                                        <div class="seal-preview-image mt-2">
                                            <img src="{{ show_image('assets/images/backend/invoiceLogo/'.$value) }}" alt="{{$value}}" class="logo-preview">
                                        </div>
                                    </div>
                                @endforeach

                            </div>
                    </div>

                    <div class="border rounded p-3">
                            <h6 class="mb-3">
                            {{translate('Cron Job & Others')}}
                            </h6>
                            <div class="row g-3">


                            <div class="col-lg-6">
                                <label for="status_expiry" class="form-label">
                                    {{translate('Product New Status Expiry')}}
                                    <span class="text-danger">
                                        ({{translate('Days')}})
                                    </span>
                                </label>
                                <input type="number" class="form-control" id="status_expiry" name="status_expiry" value="{{$general->status_expiry }}" >
                            </div>

                            <div class="col-6">
                                <label for="cron_url" class="form-label">
                                    {{translate('Cron Job Url')}}
                                </label>
                                <div class="input-group">
                                    <input id="cron_url" class="form-control"  value="curl -s {{route('cron.run')}}" >
                                    <span class="input-group-text cursor-pointer" onclick="copyUrl()">
                                        {{translate('Copy Url')}}
                                    </span>
                                </div>
                            </div>
                            </div>
                    </div>

                    <div class="text-start">
                        <button type="submit"
                            class="btn btn-success waves ripple-light"
                            id="add-btn">
                            {{translate('Submit')}}
                        </button>
                    </div>
                </form>
			</div>
		</div>
	</div>
</div>
@endsection

@push('script-include')
    <script src="{{asset('assets/backend/js/spectrum.js') }}"></script>
@endpush
@push('script-push')
<script>
  "use strict";
        function copyUrl(){
			var copyText = document.getElementById("cron_url");
			copyText.select();
			copyText.setSelectionRange(0, 99999)
			document.execCommand("copy");
			toaster('Copied the text : ' + copyText.value,'success')
        }

  	(function ($) {
        "use strict";
        $('.color_picker_show').spectrum({
            color: $(this).data('color'),
            change: function (color) {
                $(this).parent().siblings('.color_code').val(color.toHexString().replace(/^#?/, ''));
            }
        });
        $('.color_code').on('input', function () {
            var clr = $(this).val();
            $(this).parents('.input-group').find('.color_picker_show').spectrum({
                color: clr,
            });
        });

        //reset primary color
        $(document).on('click','#reset-primary-color',function(e){
            var color = '{{$general->primary_color}}'
		    $('#primary_color').val(color);
            $(this).parents('.input-group').find('.color_picker_show').spectrum({
                color: '{{$general->primary_color}}',
            });
            e.preventDefault()
        })

        //reset secondary color
        $(document).on('click','#reset-secondary-color',function(e){
            var color = '{{$general->secondary_color}}'
           $('#secondary_color').val(color);
            $(this).parents('.input-group').find('.color_picker_show').spectrum({
                color: '{{$general->secondary_color}}',
            });
            e.preventDefault()
        })
        //reset font color
        $(document).on('click','#reset-font-color',function(e){
            var color = '{{$general->font_color}}'
           $('#font_color').val(color);
            $(this).parents('.input-group').find('.color_picker_show').spectrum({
                color: '{{$general->font_color}}',
            });
            e.preventDefault()
        })

        //seller mode status update
         $(document).on('click','#seller-mode',function(e){
            updateSellerMode()
         })
      
      
          //seller mode status update
         $(document).on('click','#debug-mode',function(e){
				$.ajax({
					method:'get',
					data :$(this).attr('data-value'),
					url:"{{ route('admin.debug.mode') }}",
					dataType:'json',
					success:function(response){

						toaster(`Status Updated`,'success')
						location.reload();
					},
                    error: function (error) {

                        if(error && error.responseJSON){
                            if(error.responseJSON.errors){
                                for (let i in error.responseJSON.errors) {
                                    toaster(error.responseJSON.errors[i][0],'danger')
                                }
                            }
                            else{
                                toaster( error.responseJSON.error,'danger')
                            }
                        }
                        else{
                            toaster(error.message,'danger')
                        }

                    }
				})
         })

         //update seller mode function
          function updateSellerMode()
          {
			$.ajax({
				method:'get',
				url:"{{ route('admin.seller.mode') }}",
				dataType:'json',
				success:function(response){
					$('#seller-status').html('')
					$('#seller-status').html(`${response.status}`)
					toaster(`Seller Mode ${response.status}`,'success')
				},
                error: function (error) {

                    if(error && error.responseJSON){
                        if(error.responseJSON.errors){
                            for (let i in error.responseJSON.errors) {
                                toaster(error.responseJSON.errors[i][0],'danger')
                            }
                        }
                        else{
                            toaster( error.responseJSON.error,'danger')
                        }
                    }
                    else{
                        toaster(error.message,'danger')
                    }

                }
			})
          }
       

      

    })(jQuery);
</script>
@endpush
