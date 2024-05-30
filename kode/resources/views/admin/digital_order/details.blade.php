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
                        <li class="breadcrumb-item"><a href="{{route('admin.digital.order.product.inhouse')}}">
                            {{translate('Digital Orders')}}
                        </a></li>
                        <li class="breadcrumb-item active">
                            {{translate('Order Details')}}
                        </li>
                    </ol>
                </div>
            </div>

			<div class="row">
				<div class="col-xl-9">
					<div class="card">
						<div class="card-header border-bottom-dashed">
							<div class="d-flex gap-2 align-items-center">
								<h5 class="card-title mb-0">
									{{translate('Order Details')}}
									- {{$order->order_id}}</h5>
									@if($orderDetail->status == App\Models\Order::DELIVERED)
									  <span class="badge badge-soft-info">{{translate('Received')}}</span>
									@endif
							</div>
						</div>

						<div class="card-body">
							<div class="table-responsive table-card">
								<table class="table table-hover table-nowrap align-middle table-borderless">
									<thead class="table-light text-muted">
										<tr>
											<th scope="col">
												{{translate('Product')}}
											</th>
											<th scope="col">
												{{translate('Attribute')}}
											</th>
											<th scope="col">
												{{translate('amount')}}
											</th>

											<th scope="col" >
												{{translate("Status")}}
											</th>

											<th scope="col" >
												{{translate("Action")}}
											</th>
										</tr>
									</thead>

									<tbody>
										<tr>
											<td data-label="{{translate('Product')}}">
												@if($orderDetail->product->seller_id)
													<a href="{{route('admin.digital.product.seller.details', $orderDetail->product_id)}}" class="text-dark">{{($orderDetail->product->name)}}</a>
												@else
													<a href="{{route('admin.digital.product.attribute',$orderDetail->product->id)}}" class="text-dark">{{($orderDetail->product->name)}}</a>
												@endif
											</td>

											<td data-label="{{translate('Attribute')}}">
												{{(@$digitalProductAttributes ?@$digitalProductAttributes->name :"N/A")}}
											</td>

											<td>
												{{show_currency()}}{{round(short_amount($orderDetail->total_price))}}
											</td>

											<td data-label="{{translate('Status')}}">

												@if($orderDetail->status == 1)
													<span class="badge badge-soft-primary my-1">{{translate('Placed')}}</span>
												@elseif($orderDetail->status == 2)
													<span class="badge badge-soft-info my-1" >{{translate('Confirmed')}}</span>
												@elseif($orderDetail->status == 3)
													<span class="badge badge-soft-info my-1" >{{translate('Processing')}}</span>
												@elseif($orderDetail->status == 4)
													<span class="badge badge-soft-warning my-1" >{{translate('Shipped')}}</span>
												@elseif($orderDetail->status == 5)
													<span class="badge badge-soft-success my-1">{{translate('Delivered ')}}</span>
												@elseif($orderDetail->status == 6)
													<span class="badge badge-soft-danger my-1">{{translate('Cancel')}}</span>
												@endif

											</td>


											<td data-label="{{translate('Action')}}">
												<a class="btn-soft-primary fs-18 link-warning p-1 rounded orderstatus" title="Delivery Status update"
												data-bs-toggle="tooltip" data-bs-placement="top" data-bs-toggle="" data-bs-target="#updateorderstatus" href="javascript:void(0)" data-id="{{$orderDetail->id}}" data-status="{{$orderDetail->status}}"><i class="ri-pencil-line"></i></a>
											</td>

										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>

				<div class="col-xl-3">
					<div class="card">
						<div class="card-header border-bottom-dashed">
							<div class="d-flex">
								<h5 class="card-title flex-grow-1 mb-0">
									{{translate('Customer Details')}}
								</h5>
							</div>
						</div>
						<div class="card-body">
							<ul class="list-unstyled mb-0 vstack gap-3">
								<li>
									<div class="d-flex align-items-center">
										<div class="flex-shrink-0">
											<img src="{{show_image(file_path()['profile']['user']['path'].'/'.@$order->customer->image,file_path()['profile']['user']['size'])}}" alt="{{@$order->customer->image}}"
												class="avatar-sm rounded">
										</div>

										<div class="flex-grow-1 ms-3">
											<h6 class="fs-14 mb-1 text-break">{{(@$order->customer->name?? @$order->billing_information->email)}}</h6>
											<p class="text-muted mb-0">
												{{translate('Customer')}}
											</p>
										</div>
									</div>
								</li>

								<li>
									<i class="ri-mail-line me-2 align-middle text-muted fs-16"></i>
                                    <span class="text-break">
                                        {{(@$order->customer->email ??@$order->billing_information->email)}}
                                    </span>
								</li>

								<li>
                                    <i class="ri-phone-line me-2 align-middle text-muted fs-16"></i>
                                    <span class="text-break">
                                        {{(@$order->customer->phone ?  $order->customer->phone :'N/A')}}
                                    </span>
                                </li>
							</ul>
						</div>
					</div>

					<div class="card">
						<div class="card-header border-bottom-dashed">
							<h5 class="card-title mb-0">
                                <i class="ri-map-pin-line align-middle me-1 text-muted"></i> {{translate('Attribute Value') }}
							</h5>
						</div>

						<div class="card-body">
							@if(@$digitalProductAttributes->digitalProductAttributeValueKey->count() > 0)
						     	{{implode(",",@$digitalProductAttributes->digitalProductAttributeValueKey->pluck('value')->toArray() ??[])}}
							@else
						       <p class="text-center">
								  {{translate("No Attribute Value Found")}}
							   </p>
							@endif
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>


	<div class="modal fade" id="updateorderstatus" tabindex="-1" aria-labelledby="updateorderstatus" aria-hidden="true">
		<div class="modal-dialog">
			<form  action="{{route('admin.digital.order.payment.status')}}" method="post">
				@csrf
				<input type="hidden" name="id">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">{{translate('Status Update')}}</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>

					<div class="modal-body">
						<div class="form-group">
							<label for="ostatus" class="form-label">{{translate('Status')}} <span class="text-danger">*</span></label>
							<select class="form-select" name="status" id="ostatus">
								<option value="1">{{translate('Nothing Selected')}}</option>
								<option value="2">{{translate('Confirmed')}}</option>
								<option value="3">{{translate('Processed')}}</option>
								<option value="4">{{translate('Shipped')}}</option>
								<option value="5">{{translate('Delivered')}}</option>
								<option value="6">{{translate('Cancel')}}</option>
							</select>
						</div>
						<div class="status_html"></div>
					</div>

					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{translate('Cancel')}}</button>
						<button type="submit" class="btn btn-primary">{{translate('Update')}}</button>
					</div>
				</div>
			</form>
		</div>
	</div>

@endsection
@push('script-push')
<script>
	(function($){
       	"use strict";
		$('.orderstatus').on('click', function(){
			var modal = $('#updateorderstatus');
			modal.find('input[name=id]').val($(this).data('id'));
			modal.find('select[name=status]').val($(this).data('status'));
			modal.modal('show');
		});
	})(jQuery);
</script>
@endpush
