@extends('admin.layouts.app')
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
                    <li class="breadcrumb-item"><a href="{{route('admin.gateway.payment.method')}}">
                        {{translate('Payment Methods')}}
                    </a></li>
                    <li class="breadcrumb-item active">
                        {{translate('Update')}}
                    </li>
                </ol>
            </div>
        </div>


        <div class="card">
            <div class="card-header border-bottom-dashed">
                <div class="row g-4 align-items-center">
                    <div class="col-sm">
                        <div>
                            <h5 class="card-title mb-0">
                                {{translate('Update')}}-{{$paymentMethod->name}}
                            </h5>
                        </div>
                    </div>
                </div>
            </div>

             <div class="card-body">
                <form action="{{route('admin.gateway.payment.update', $paymentMethod->id)}}" method="POST"  enctype="multipart/form-data">
                    @csrf

                    <div class="row g-3">
						<h6 class="fw-bold">{{translate('Payment Gateway Setting')}}</h6>
						@foreach($paymentMethod->payment_parameter as $key => $parameter)
							<div class="col-lg-6">
								<label for="{{$key}}" class="form-label">{{ucwords(str_replace('_', ' ', $key))}} <span class="text-danger">*</span></label>
								<input type="text" name="method[{{$key}}]" id="{{$key}}" value="{{$parameter}}" class="form-control"   placeholder='{{"Enter ".ucwords(str_replace("_", " ", $key))}}' required>
							</div>
						@endforeach
						<div class="col-lg-6">
							<label for="status" class="form-label">{{translate('Status')}} <span class="text-danger">*</span></label>
							<select class="form-select" name="status" id="status" required>
								<option value="1" @if($paymentMethod->status == 1) selected @endif>{{translate('Active')}}</option>
								<option value="2" @if($paymentMethod->status == 2) selected @endif>{{translate('Inactive')}}</option>
							</select>
						</div>
						<div class="col-lg-6">
							<label for="currency_id" class="form-label">{{translate('Select Currency')}} <span class="text-danger">*</span></label>
							<select class="form-select" name="currency_id" id="currency_id" required>
								<option value="">{{translate('Select One')}}</option>
								@foreach($currencies as $currency)
									<option value="{{$currency->id}}" @if($paymentMethod->currency_id == $currency->id) selected @endif data-rate="{{short_amount($currency->rate)}}">{{translate($currency->name)}}</option>
								@endforeach
							</select>
						</div>
						<div class="col-lg-6">
							<label for="image" class="form-label">{{translate('Image')}} <span class="text-danger">*</span></label>
							<input type="file" name="image" id="image" class="form-control">

							<div id="image-preview-section">
                                <img alt='{{$paymentMethod->image}}' class="mt-2 rounded  d-block avatar-xl img-thumbnail"
                                    src="{{show_image(file_path()['payment_method']['path'].'/'.$paymentMethod->image,file_path()['payment_method']['size']) }}">
                            </div>
						</div>
						<div class="col-lg-6">
							<label for="percent_charge" class="form-label">{{translate('Percent Charge')}} <span class="text-danger">*</span></label>
							<div class="input-group">
								  <input type="text" class="form-control" id="percent_charge" name="percent_charge" value="{{round($paymentMethod->percent_charge)}}" placeholder="{{translate('Enter Number')}}" >
								  <span class="input-group-text" >%</span>
							</div>
						</div>
						<div class="col-lg-6">
							<label for="rate" class="form-label">{{translate('Currency Rate')}} <span class="text-danger">*</span></label>
							<div class="input-group mb-3">
								  <span class="input-group-text">1 {{$general->currency_name}} = </span>
								  <input type="text" name="rate" value="{{round($paymentMethod->rate)}}" class="form-control" aria-label="Amount (to the nearest dollar)">
								  <span class="input-group-text limittext">{{(@$paymentMethod->currency->name)}}</span>
							</div>
						</div>
                        <div class="col-12">
                            <div class="text-start">
                                <button type="submit" class="btn btn-success">
                                    {{translate('Update')}}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
             </div>
        </div>

	</div>
</div>

@endsection

@push('script-push')
<script>
	'use strict'
	$("#currency_id").on('change', function(){
		var value = $(this).find("option:selected").text();
		$(".limittext").text(value);
		var currencyrate = $('select[name=currency_id] :selected').data('rate');
		$('input[name=rate]').val(currencyrate);
	});
</script>
@endpush
