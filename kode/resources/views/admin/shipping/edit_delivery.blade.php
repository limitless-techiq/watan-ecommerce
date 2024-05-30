@extends('admin.layouts.app')
@push('style-include')
<link href="{{asset('assets/backend/css/summnernote.css')}}" rel="stylesheet" type="text/css" />
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
                    <li class="breadcrumb-item"><a href="{{route('admin.shipping.delivery.index')}}">
                        {{translate('Shipping Delivery')}}
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
								{{translate('Update Shipping Delivery')}}
							</h5>
						</div>
					</div>
				</div>
			</div>

			<div class="card-body">
				<form action="{{route('admin.shipping.delivery.update', $shippingDelivery->id)}}" method="POST" enctype="multipart/form-data">
					@csrf
					<div class="row g-3">
						<div class="col-lg-6">
							<div >
								<label for="name" class="form-label">
									{{translate('Name')}} <span  class="text-danger">*</span>
								</label>
								<input type="text" name="name" id="name" value="{{$shippingDelivery->name}}" class="form-control" placeholder="Enter Name" required="">
							</div>
						</div>

						<div class="col-lg-6">
							<div >
								<label for="method_id" class="form-label">
									{{translate('Shipping Method')}} <span  class="text-danger" >*</span>
								</label>

								<select class="form-select" name="method_id" id="method_id" required>
									<option value="">{{translate('Select One')}}</option>
									@foreach($methods as $method)
										<option {{$shippingDelivery->method_id == $method->id ? "selected" : ""}}   value="{{$method->id}}">{{translate($method->name)}}</option>
									@endforeach
								</select>

							</div>
						</div>

						<div class="col-lg-4">
							<div >
								<label for="duration" class="form-label">
									{{translate('Duration')}} <span  class="text-danger" >*</span>
								</label>
								<div class="input-group">
									<input type="text" class="form-control"  name="duration" id="duration" value="{{$shippingDelivery->duration}}">
									<span class="input-group-text" >{{translate('Days')}} </span>
								</div>
							</div>
						</div>

						<div class="col-lg-4">
							<div >
								<label for="price" class="form-label">
									{{translate("Price")}} <span  class="text-danger" >*</span>
								</label>
								<div class="input-group">
									<input type="text" class="form-control" id="price" value="{{round($shippingDelivery->price)}}" name="price"  placeholder="Enter Amount" aria-label="Enter Amount" aria-describedby="basic-addon2">
									<span class="input-group-text" >
										{{$general->currency_name}}
									</span>
								</div>
							</div>
						</div>

						<div class="col-lg-4">
							<div >
								<label for="status" class="form-label">
									{{translate('Status')}} <span  class="text-danger" >*</span>
								</label>
								<select class="form-select" name="status" id="status" required>
									<option  value="">{{translate('Select One')}}</option>
									<option {{$shippingDelivery->status == 0 ? "selected" : "" }}  value="0">{{translate('Inactive')}}</option>
									<option {{$shippingDelivery->status == 1 ? "selected" : "" }}    value="1">{{translate('Active')}}</option>
								</select>
							</div>
						</div>

						<div class="col-12 text-editor-area">
							<div>
								<label for="description" class="form-label">
									{{translate('Description')}} <span class="text-danger"  >*</span>
								</label>
								<textarea class="form-control text-editor" name="description" rows="10" id="description" placeholder="{{translate('Enter Description')}}" required="">
									{{$shippingDelivery->description}}
								</textarea>
								@if( $openAi->status == 1)
										<button type="button" class="ai-generator-btn mt-3 ai-modal-btn" >
											<span class="ai-icon btn-success waves ripple-light">
													<span class="spinner-border d-none" aria-hidden="true"></span>

													<i class="ri-robot-line"></i>
											</span>

											<span class="ai-text">
												{{translate('Generate With AI')}}
											</span>
										</button>
								@endif
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
@push('script-include')
	<script src="{{asset('assets/backend/js/summnernote.js')}}"></script>
	<script src="{{asset('assets/backend/js/editor.init.js')}}"></script>
@endpush
