@extends('admin.layouts.app')
@push('style-include')
<link href="{{asset('assets/backend/css/summnernote.css')}}" rel="stylesheet" type="text/css" />
@endpush
@section('main_content')
<div class="page-content">
	<div class="container-fluid">

        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">
                {{$title}}
            </h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">
                        {{translate('Dashboard')}}
                    </a></li>
                    <li class="breadcrumb-item active">
                        {{translate('Shipping Delivary')}}
                    </li>
                </ol>
            </div>
        </div>

		<div class="card">
			

			<div class="card-header border-0">
				<div class="row g-4 align-items-center">
					<div class="col-sm">
						<h5 class="card-title mb-0">
							{{translate('Shipping Delivary List')}}
						</h5>
					</div>
					<div class="col-sm-auto">
						<div class="d-flex flex-wrap align-items-start gap-2">
							<button type="button" class="btn btn-success add-btn w-100 waves ripple-light"
								data-bs-toggle="modal" id="create-btn" data-bs-target="#addDeliveryMethod"><i
									class="ri-add-line align-bottom me-1"></i>
								  {{translate('Add New')}}
							   </button>
						</div>
					</div>
				</div>
			</div>


			<div class="card-body border border-dashed border-end-0 border-start-0">
				<form action="{{route('admin.shipping.delivery.index')}}" method="get">
					<div class="row g-3">
						<div class="col-xl-4 col-lg-5">
							<div class="search-box">
								<input type="text" name="search" value="{{request()->input('search')}}" class="form-control search"
									placeholder="{{translate('Search by name,method')}}">
								<i class="ri-search-line search-icon"></i>
							</div>
						</div>

						<div class="col-xl-2 col-lg-2 col-sm-4 col-6">
							<div>
								<button type="submit" class="btn btn-primary w-100 waves ripple-light"> <i
										class="ri-equalizer-fill me-1 align-bottom"></i>
									{{translate('Search')}}
								</button>
							</div>
						</div>

						<div class="col-xl-2 col-lg-2 col-sm-4 col-6">
							<div>
								<a href="{{route('admin.shipping.delivery.index')}}" class="btn btn-danger w-100 waves ripple-light"> <i
										class="ri-refresh-line me-1 align-bottom"></i>
									{{translate('Reset')}}
								</a>
							</div>
						</div>

					
					</div>
				</form>
			</div>

			<div class="card-body">
				<div class="table-responsive table-card">
					<table class="table table-hover table-centered align-middle table-nowrap">
						<thead class="text-muted table-light">
							<tr>
								<th scope="col">#</th>
								<th scope="col">
									{{translate('Name')}}
								</th>
								<th scope="col">
									{{translate('Method')}}
								</th>
								<th scope="col">
									{{translate('Duration')}}
								</th>
								<th scope="col">
									{{translate('Price')}}
								</th>
								<th scope="col">
									{{translate('Status')}}
								</th>
								<th scope="col">
									{{translate('Action')}}
								</th>
							</tr>
						</thead>

						<tbody>
							@forelse($shippingDeliverys as $shippingDelivery)
								<tr>
									<td class="fw-medium">
										{{$loop->iteration}}
									</td>

									<td>
										{{$shippingDelivery->name}}
									</td>

									<td>
										{{@$shippingDelivery->method?$shippingDelivery->method->name :'N/A'}}
									</td>

									<td>
										{{$shippingDelivery->duration}} {{translate('Days')}}
									</td>

									<td>
										{{round(($shippingDelivery->price))}} {{$general->currency_name}}
									</td>

									<td>
										@if($shippingDelivery->status == 1)
											<span class="badge badge-soft-success">{{translate('Active')}}</span>
										@else
											<span class="badge badge-soft-danger">{{translate('Inactive')}}</span>
										@endif
									</td>

									<td>
										<div class="hstack justify-content-center gap-3">
											@if(permission_check('update_settings'))

												<a title="{{translate('Update')}}"   data-bs-toggle="tooltip" data-bs-placement="top"   class="link-warning fs-18 " href="{{route('admin.shipping.delivery.edit', $shippingDelivery->id)}}"><i class="ri-pencil-fill"></i>
												</a>
												<a href="javascript:void(0);"  title="{{translate('Delete')}}"   data-bs-toggle="tooltip" data-bs-placement="top"    data-href="{{route('admin.shipping.delivery.delete',$shippingDelivery->id)}}" class="delete-item fs-18 link-danger">
												<i class="ri-delete-bin-line"></i></a>

											@endif
										</div>
									</td>
								</tr>
							@empty
								<tr>
									<td class="border-bottom-0" colspan="100">
										@include('admin.partials.not_found')
									</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>

</div>

<div class="modal fade" id="addDeliveryMethod" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addDeliveryMethod" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header bg-light p-3">
				<h5 class="modal-title" >{{translate('Add Delivery Method')}}
				</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"
					aria-label="Close" id="close-modal"></button>
			</div>
			<div class="modal-body">
				<form action="{{route('admin.shipping.delivery.store')}}" method="POST" enctype="multipart/form-data">
					@csrf
					<div class="row g-3">
						<div class="col-lg-6">
							<div>
								<label for="name" class="form-label">
									{{translate('Name')}} <span  class="text-danger" >*</span>
								</label>
								<input value="{{old('name')}}" type="text" name="name" id="name" class="form-control" placeholder="Enter Name" required="">
							</div>
						</div>

						<div class="col-lg-6">
							<label for="method_id" class="form-label">
								{{translate('Shipping Method')}} <span  class="text-danger" >*</span>
							</label>
							<select class="form-select" name="method_id" id="method_id" required>
								<option value="">{{translate('Select One')}}</option>
								@foreach($methods as $method)
									<option {{old('method_id') == $method->id ? "selected" : ""}}   value="{{$method->id}}">{{translate($method->name)}}</option>
								@endforeach
							</select>
						</div>

						<div class="col-lg-4">
							<label for="duration" class="form-label">
								{{translate('Duration')}} <span  class="text-danger" >*</span>
							</label>
							<div class="input-group">
								<input  type="text" class="form-control"  name="duration" id="duration" placeholder="{{translate('Enter Duration')}}" value="{{old('duration')}}" >
								<span class="input-group-text" >{{translate('Days')}}</span>
							</div>
						</div>

						<div class="col-lg-4">
							<label for="price" class="form-label">
								{{translate("Price")}} <span  class="text-danger" >*</span>
							</label>
							<div class="input-group">
								<input type="text" class="form-control" id="price" value="{{old('price')}}" name="price"  placeholder="Enter Amount">
								<span class="input-group-text" >
									{{$general->currency_name}}
								</span>
							</div>
						</div>

						<div class="col-lg-4">
							<label for="status" class="form-label">
								{{translate('Status')}} <span  class="text-danger" >*</span>
							</label>
							<select class="form-select" name="status" id="status" required>
								<option  value="">{{translate('Select One')}}</option>
								<option {{old('status') == 0 ? "selected" : "" }}  value="0">{{translate('Inactive')}}</option>
								<option {{old('status') == 1 ? "selected" : "" }}    value="1">{{translate('Active')}}</option>
							</select>
						</div>

						<div class="text-editor-area">
							<label for="description" class="form-label">
								{{translate('Decription')}} <span class="text-danger"  >*</span>
							</label>
							<textarea id="description" class=" form-control text-editor" name="description" rows="10" placeholder="{{translate('Enter Description')}}" required="">{{old('description')}}</textarea>

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
					<div>
						<div class="modal-footer px-0 pt-3 pb-0">
							<div class="hstack gap-2 justify-content-end">
								<button type="button"
									class="btn btn-danger waves ripple-light"
									data-bs-dismiss="modal">
								     {{translate('Close')}}

								</button>
								<button type="submit" class="btn btn-success waves ripple-light">
							        {{translate('Add')}}
						     	</button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

@include('admin.modal.delete_modal')
@endsection
@push('script-include')
	<script src="{{asset('assets/backend/js/summnernote.js')}}"></script>
	<script src="{{asset('assets/backend/js/editor.init.js')}}"></script>
@endpush

