@extends('seller.layouts.app')
@section('main_content')
	<div class="page-content">
		<div class="container-fluid">

            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">
                    {{translate('Order Details')}}
                </h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{route('seller.dashboard')}}">
                            {{translate('Home')}}
                        </a></li>
                        <li class="breadcrumb-item"><a href="{{route('seller.digital.order.index')}}">
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
							<div class="d-flex gap-2 align-items-center"        >
								<h5 class="card-title mb-0">
									{{translate('Order')}} -
									{{$order->order_id}}</h5>
									@if($orderDetail->status == App\Models\Order::DELIVERED)
									  <span class="badge badge-soft-danger">{{translate('Received')}}</span>
									@endif
							</div>
						</div>

						<div class="card-body">
							<div class="table-responsive table-card">
								<table class="table table-hover table-nowrap align-middle">
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
												{{translate("Action")}}
											</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td data-label="{{translate('Product')}}">
												{{$orderDetail->product->name}}
											</td>
											<td data-label="{{translate('Attribute')}}">
												{{(@$digitalProductAttributes ?@$digitalProductAttributes->name :"N/A")}}
											</td>
											<td>
												{{show_currency()}}{{round(short_amount($orderDetail->total_price))}}
											</td>

											<td data-label="{{translate('Action')}}">
												@if($orderDetail->status == App\Models\Order::DELIVERED)
													<span class="badge badge-soft-success my-1">{{translate('Received')}}</span>
												@else
													<span class="badge badge-soft-danger my-1">{{translate('N/A')}}</span>
												@endif
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
											<h6 class="fs-14 mb-1">{{(@$order->customer->name?? @$order->billing_information->email)}}</h6>
											<p class="text-muted mb-0">
												{{translate('Customer')}}
											</p>
										</div>
									</div>
								</li>
								<li>
									<i class="ri-mail-line me-2 align-middle text-muted fs-16"></i>{{(@$order->customer->email ?? @$order->billing_information->email)}}
								</li>
								<li><i class="ri-phone-line me-2 align-middle text-muted fs-16"></i>{{(@$order->customer->phone ?  $order->customer->phone :'N/A')}}</li>
							</ul>
						</div>
					</div>

					<div class="card">
						<div class="card-header border-bottom-dashed">
							<h5 class="card-title mb-0"><i
									class="ri-map-pin-line align-middle me-1 text-muted"></i>
									{{translate('Attribute Value')}}
							</h5>
						</div>
						<div class="card-body">
							@if(@$digitalProductAttributes->digitalProductAttributeValueKey)
						     	{{implode(",",@$digitalProductAttributes->digitalProductAttributeValueKey->pluck('value')->toArray() ??[])}}
							@else
							   {{translate('N/A')}}
							@endif

						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
