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
                    <li class="breadcrumb-item"><a href="{{route('admin.inhouse.order.index')}}">
                            {{translate('Orders')}}
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
								{{translate('Order')}} -
								{{$order->order_id}}
                            </h5>

                            @if($order->payment_status == App\Models\Order::UNPAID)
                                <span class="badge badge-soft-danger">{{translate('Unpaid')}}</span>
                            @elseif($order->payment_status == App\Models\Order::PAID)
                                <span class="badge badge-soft-success">{{translate('Paid')}}</span>
                            @endif
                            &
                            @if($order->status == App\Models\Order::PLACED)
                                <span class="badge badge-soft-primary">{{translate('Placed')}}</span>
                            @elseif($order->status == App\Models\Order::CONFIRMED)
                                <span class="badge badge-soft-info">{{translate('Confirmed')}}</span>
                            @elseif($order->status == App\Models\Order::PROCESSING)
                                <span class="badge badge-soft-secondary">{{translate('Processing')}}</span>
                            @elseif($order->status == App\Models\Order::SHIPPED)
                                <span class="badge badge-soft-warning">{{translate('Shipped')}}</span>
                            @elseif($order->status == App\Models\Order::DELIVERED)
                                <span class="badge badge-soft-success">{{translate('Delivered')}}</span>
                            @elseif($order->status == App\Models\Order::CANCEL)
                                <span class="badge badge-soft-danger">{{translate('Cancel')}}</span>
                            @endif
						</div>
					</div>

					<div class="card-body">
						<div class="table-responsive table-card">
							<table class="table table-nowrap align-middle table-borderless mb-0">
								<thead class="table-light text-muted">
									<tr>
										<th scope="col">
											{{translate('Product Name')}}
										</th>
										<th scope="col">
											{{translate('Item Price')}}
										</th>
										<th scope="col">
											{{translate('Qty')}}
										</th>
										<th scope="col">
											{{translate('Total')}}
										</th>
										<th scope="col">
											{{translate('Status')}}
										</th>
										<th scope="col" >
											{{translate("Action")}}
										</th>
									</tr>
								</thead>

								<tbody>
									@php
									  $subtotal = 0;
									@endphp
									@foreach($orderDeatils as $orderDetail)
										<tr>
											<td>
												<div class="d-flex align-items-center">
													<div class="flex-shrink-0">
														<img alt="{{@$orderDetail->product->featured_image}}" class="avatar-md rounded img-thumbnail" src="{{show_image(file_path()['product']['featured']['path'].'/'.@$orderDetail->product->featured_image,file_path()['product']['featured']['size'])}}" >
													</div>
													<div class="flex-grow-1 ms-3">
														<h5 class="fs-14">
															<a href="{{route('admin.item.product.inhouse.details', $orderDetail->product_id)}}"
																class="text-body">{{$orderDetail->product->name}}
															</a>
														</h5>
														<div class="d-flex align-items-center">
                                                            <span class="btn btn-outline-primary btn-sm rounded py-0 me-2">{{$orderDetail->attribute}}</span>
                                                        </div>
													</div>
												</div>
											</td>
											<td>
												 {{show_currency()}}{{round(short_amount($orderDetail->product->discount))}}
											</td>
											<td>
												{{$orderDetail->quantity}}
											</td>

											<td>
												{{show_currency()}}{{round(short_amount($orderDetail->total_price))}}
											</td>
											<td data-label="{{translate('Status')}}">
												@if($orderDetail->status == App\Models\Order::PLACED)
													<span class="badge badge-soft-primary my-1">{{translate('Placed')}}</span>
												@elseif($orderDetail->status == App\Models\Order::CONFIRMED)
													<span class="badge badge-soft-info my-1">{{translate('Confirmed')}}</span>
												@elseif($orderDetail->status == App\Models\Order::PROCESSING)
													<span class="badge badge-soft-secondary my-1">{{translate('Processing')}}</span>
												@elseif($orderDetail->status == App\Models\Order::SHIPPED)
													<span class="badge badge-soft-warning my-1">{{translate('Shipped')}}</span>
												@elseif($orderDetail->status == App\Models\Order::DELIVERED)
													<span class="badge badge-soft-success my-1">{{translate('Delivered')}}</span>
												@elseif($orderDetail->status == App\Models\Order::CANCEL)
													<span class="badge badge-soft-danger my-1">{{translate('Cancel')}}</span>
												@endif
											</td>
											<td data-label="{{translate('Action')}}">
												<a class="btn-soft-primary fs-18 link-warning p-1 rounded orderstatus" title="Delivery Status update"
												data-bs-toggle="tooltip" data-bs-placement="top" data-bs-toggle="modal" data-bs-target="#updateorderstatus" href="javascript:void(0)" data-id="{{$orderDetail->id}}" data-status="{{$orderDetail->status}}"><i class="ri-pencil-line"></i></a>
											</td>
										</tr>
										@php
									     	$subtotal += $orderDetail->total_price;
								     	@endphp
									@endforeach
									<tr class="border-top border-top-dashed">
										<td colspan="4"></td>
										<td colspan="3" class="fw-medium p-0">
											<table class="table table-borderless mb-0">
												<tbody>
													<tr>
														<td class="text-start">
															{{translate('Total Amount')}}
															 :</td>
														<td class="text-end">
															{{show_currency()}}{{round(short_amount($subtotal))}}
														</td>
													</tr>

													<tr>
														<td class="text-start">
															{{translate('Shipping Cost')}}

														</td>
														<td class="text-end">

															@if($order->shipping_deliverie_id)
															 {{show_currency()}}{{round(short_amount($order->shipping_charge))}}
															@else
														    	0
															@endif

														</td>
													</tr>

													<tr>
														<td class="text-start">
															{{translate('Coupon Discount')}}

															:</td>
														<td class="text-end">
															@if($order->discount)

															  {{show_currency()}}{{round(short_amount($order->discount))}}

															@else
														    	0
															@endif
														</td>
													</tr>

													<tr class="border-top border-top-dashed">
														<th scope="row" class="fw-bold text-start">{{translate('Total')}}:</th>

														<th class="text-end">
															{{show_currency()}}{{round(short_amount($order->amount))}}
														</th>
													</tr>
												</tbody>
											</table>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>

				<div class="card">
					<div class="card-header border-bottom-dashed">
						<h5 class="card-title mb-0">
                            <i class="ri-map-pin-line align-middle me-1 text-muted"></i>
                            {{translate('Product Status Update')}}
						</h5>
					</div>

					<div class="card-body">
						<form action="{{route('admin.inhouse.order.status.update', $order->id)}}" method="POST" enctype="multipart/form-data">
							@csrf
							<div class="row">
								<div class="mb-3 col-lg-6">
									<label for="payment_status" class="form-label">{{translate('Payment Status')}} <span class="text-danger">*</span></label>
									<select class="form-select" name="payment_status" id="payment_status">
										<option value="2" @if($order->payment_status == 2) selected @endif>{{translate('Paid')}}</option>
										<option value="1" @if($order->payment_status == 1) selected @endif>{{translate('Unpaid')}}</option>
									</select>
									<div class="form-group mt-2">
										<textarea name="payment_note" placeholder="Write short note" class="form-control"></textarea>
									</div>
								</div>
								<div class="mb-3 col-lg-6">
									<label for="status" class="form-label">{{translate('Delivery Status')}} <span class="text-danger">*</span></label>
									<select class="form-select" name="status" id="status">
										<option value=""  @if($order->status == 1) selected @endif>{{translate('Nothing Selected')}}</option>
										<option value="2" @if($order->status == 2) selected @endif>{{translate('Confirmed')}}</option>
										<option value="3" @if($order->status == 3) selected @endif>{{translate('Processed')}}</option>
										<option value="4" @if($order->status == 4) selected @endif>{{translate('Shipped')}}</option>
										<option value="5" @if($order->status == 5) selected @endif>{{translate('Delivered')}}</option>
										<option value="6" @if($order->status == 6) selected @endif>{{translate('Cancel')}}</option>
									</select>
									<div class="form-group mt-2">
										<textarea name="delivery_note" placeholder="Write short note" class="form-control"></textarea>
									</div>
								</div>
							</div>
							<button type="submit" class="btn btn-success btn-xl fs-6 px-4 text-light mb-4">{{translate('Save')}}</button>
						</form>

						@foreach ($orderStatus as $status)
							<div class="row">
								<div class="col-lg-6">
									<ul class="list-group">
										<li class="list-group-item d-flex justify-content-between align-items-center mb-3">
											<div>
												<p class="d-block pmd-list-subtitle mb-0">{{translate('Note')}} : {{($status->payment_note) }}</p>
												<span class="text-muted fs-12">{{($status->created_at)->format('d-m-Y')}}</span>
											</div>
											<span class="badge  bg-{{ $status->payment_status == 1 ? 'danger' :'success'}}">
												{{ $status->payment_status == 1 ? ('Unpaid') :('paid')}}
											</span>
										</li>
									</ul>
								</div>

								<div class="col-lg-6">
									<ul class="list-group">
										<li class="list-group-item d-flex justify-content-between align-items-center mb-3">
											<div>
												<p class="d-block pmd-list-subtitle mb-0">{{translate('Note')}} : {{ ($status->delivery_note) }}</p>
												<span class="text-muted fs-12">{{($status->created_at)->format('d-m-Y')}}</span>
											</div>
											<span class="badge
											@if($status->delivery_status == 1)
											    bg-primary
											@elseif($status->delivery_status == 2 )
											    bg-secondary
											 @elseif($status->delivery_status == 3 )
											    bg-info
											 @elseif($status->delivery_status == 4 )
											    bg-dark
											  @elseif($status->delivery_status == 5 )
											    bg-success
											  @elseif($status->delivery_status == 6 )
											    bg-danger
											 @else
											    bg-warning
											@endif

											">
												@if($status->delivery_status == 1 )
														{{translate('Placed')}}
													@elseif($status->delivery_status == 2 )
														{{translate('Confirmed')}}
													@elseif($status->delivery_status == 3)
														{{translate('Processing')}}
													@elseif($status->delivery_status == 4 )
														{{translate('Shipped')}}
													@elseif( $status->delivery_status == 5)
														{{translate('Delivered')}}
													@elseif( $status->delivery_status == 6)
														{{translate('Cencal')}}
														@else
														{{translate('N/A')}}
													@endif
											</span>
										</li>
									</ul>
								</div>
							</div>
						@endforeach
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
										<img src="{{show_image(file_path()['profile']['user']['path'].'/'.@$order->customer->image,file_path()['profile']['user']['size'])}}" alt="{{@$order->customer->name}}"
											class="avatar-sm rounded">
									</div>
									<div class="flex-grow-1 ms-3">
										<h6 class="fs-14 mb-1">{{(@$order->customer->name ?? @$order->billing_information->frist_name)}}</h6>
										<p class="text-muted mb-0">
											{{translate('Customer')}}
										</p>
									</div>
								</div>
							</li>

							<li>
								<i class="ri-mail-line me-2 align-middle text-muted fs-16 text-break"></i>
                                <span class="text-break">
                                    {{(@$order->customer->email ?? @$order->billing_information->email)}}
                                </span>
							</li>

							<li>
                                <i class="ri-phone-line me-2 align-middle text-muted fs-16"></i>
                                <span class="text-break">{{(@$order->customer->phone ??  @$order->billing_information->phone)}}</span>
                            </li>
						</ul>
					</div>
				</div>

				@if(@$order->billing_information )
					<div class="card">
						<div class="card-header border-bottom-dashed">
							<h5 class="card-title mb-0">
                                <i
									class="ri-map-pin-line align-middle me-1 text-muted"></i>
									{{translate('Billing Address')}}
							</h5>
						</div>

						<div class="card-body">
							<ul class="list-unstyled vstack gap-2 fs-13 mb-0">
								@foreach (@$order->billing_information as $key => $value )
                                    <li>
                                        <span class="font-weight-bold text-break">{{k2t($key)}} : {{$value}}</span>
                                    </li>
								@endforeach
							</ul>
						</div>
					</div>
				@endif

				<div class="card">
					<div class="card-header border-bottom-dashed">
						<h5 class="card-title mb-0">
                            <i class="ri-map-pin-line align-middle me-1 text-muted">
                            </i>
                             {{translate('Shipping Address')}}
						</h5>
					</div>

					<div class="card-body">
						<ul class="list-unstyled vstack gap-2 fs-13 mb-0">
							<li >
								<span>{{(@$order->shipping->name)}}</span>
							</li>
							<li >
								<span class="font-weight-bold">{{(@$order->shipping->method->name ?? 'N/A')}}</span>
							</li>
							<li >
								<span class="font-weight-bold">{{(@$order->shipping->duration)}} {{translate('Days')}}</span>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="updateorderstatus" tabindex="-1" aria-labelledby="updateorderstatus" aria-hidden="true">
    <div class="modal-dialog">
        <form  action="{{route('admin.inhouse.order.product.status.update')}}" method="post">
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

