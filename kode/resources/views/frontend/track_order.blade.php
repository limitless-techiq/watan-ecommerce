@extends('frontend.layouts.app')
@section('content')
<section class="pt-80 pb-80">
	<div class="Container">
		<div class="tracking-container">
			<p class="fs-5">
				{{translate("To track your order please enter your Order ID in the box below
				and press the “Track Button”. This was given to you on your
				receipt and in the confirmation email you should have received")}}
			</p>

			<form class="tracking-form">
				<div class="tracking-id">
					<label for="trackingId" class="form-label">
						{{translate("TRACKING ID ")}}
					    <span class="text-danger">*</span>
                    </label>
					<input type="text" name="order_number"  id="trackingId" value="{{$orderNumber ?? null}}" placeholder="Enter order ID" class="form-control"/>
				</div>
				<div class="tracking-id">
					<label for="email" class="form-label">
						{{translate("BILLING EMAIL")}}
					</label>
					<input  type="email" name="email" id="email" class="form-control" placeholder="{{translate('Email you using during checkout')}}" />
				</div>
				<button class="tracking-submit-btn">
					{{translate("Track Now")}}
				</button>
			</form>

			@if($order)
				<div class="row g-4">
                    <div class="col-xl-9 col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-3">
                                            <h4 class="card-title">
                                                {{translate('Tracking order lists')}}
                                            </h4>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="tracking-tabs-content">
                                    <div class="tracking-product">
                                        <div class="order-processing">
                                            <div class="tracking-wrapper">
                                                <div class="empty-bar"></div>
                                                <div class="color-bar @if(@$order->status == 2)
                                                    order-confirm
                                                @elseif(@$order->status == 3)
                                                     courier
                                                @elseif(@$order->status == 4)
                                                     way
                                                @elseif(@$order->status == 5)
                                                    pickup
                                                @endif "></div>
                                                <ul>
                                                    <li class="order-status">
                                                        <div class="el"><i class="fa-solid fa-circle-check"></i></div>
                                                        <p class="order-status-text">
                                                            {{translate("Order Confirm")}}
                                                            <span></span></p>
                                                    </li>
                                                    <li class="order-status">
                                                        <div class="el"><i class="fa-solid fa-box"></i></div>
                                                        <p class="order-status-text">
                                                            {{translate("Picked by courier")}}
                                                        <span></span></p>
                                                    </li>
                                                    <li class="order-status">
                                                        <div class="el"><i class="fa-solid fa-truck-fast"></i></div>
                                                        <p class="order-status-text">
                                                            {{translate("On The Way")}}
                                                         <span></span></p>
                                                    </li>
                                                    <li class="order-status">
                                                        <div class="el">
                                                        <i class="fa-solid fa-hand-holding-droplet"></i>
                                                    </div>
                                                        <p class="order-status-text">
                                                            {{translate("Reday for pickup")}}
                                                         <span></span></p>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="tracking-order-info">
                                            <p class="order-id">
                                                {{translate("Order ID")}}
                                                : <span>#{{@$order->order_id}}</span></p>
                                            <div class="tracking-order-status">
                                                <div class="tracking-order-status-item">
                                                    <p> {{translate("Estimated Delivery time")}} :</p>
                                                    <span>{{get_date_time(@$order->created_at, 'd M Y')}}</span>
                                                </div>
                                                <div class="tracking-order-status-item">
                                                    <p>{{translate("Shipping by")}}  :</p>
                                                    <span>{{$order->shipping_deliverie_id != null ? $order->shipping->method->name : 'N/A'}}</span>
                                                </div>
                                                <div class="tracking-order-status-item">
                                                    <p> {{translate("Status")}} :</p>
                                                    @if(@$order->status == 1)
                                                        <span>{{translate('Order Placed')}}</span>
                                                    @elseif(@$order->status == 2)
                                                        <span>{{translate('Order Confirm')}}</span>
                                                    @elseif(@$order->status == 3)
                                                        <span>{{translate('Picked by courier')}}</span>
                                                    @elseif(@$order->status == 4)
                                                        <span>{{translate('On The Way')}}</span>
                                                    @elseif(@$order->status == 5)
                                                        <span>{{translate('Reday for pickup')}}</span>
                                                    @elseif($order->status == 6)
                                                        <span>{{translate('Order Cancel')}}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            @if($order->OrderDetails->isNotEmpty())
                                                <div class="table-responsive">
                                                    <table class="table table-nowrap align-middle mt-0">
                                                        <thead class="table-light">
                                                            <tr class="text-muted fs-14">
                                                                <th scope="col">
                                                                    {{translate("Product")}}
                                                                </th>

                                                                <th scope="col" class="text-center">
                                                                    {{translate("Total Price")}}
                                                                </th>

                                                                <th scope="col" class="text-end">
                                                                    {{translate("Date")}}
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                 
                                                            @foreach($order->OrderDetails as $orderDetail)
                                                             
                                                                @if(@$orderDetail->product)
                                                          
                                                                    <tr class="tr-item fs-14">
                                                                        <td >
                                                                            <div class="order-item">
                                                                                <div class="order-item-img">
                                                                                    <img src="{{show_image(file_path()['product']['featured']['path'].'/'.@$orderDetail->product->featured_image,file_path()['product']['featured']['size'])}}"
                                                                                        alt="{{@$orderDetail->product->featured_image}}" />
                                                                                </div>
                                                                                <div class="order-item-content">
                                                                                    <div class="order-product-details">
                                                                                        <h5>
                                                                                            <a  href="{{route('product.details',[make_slug($orderDetail->product->name),$orderDetail->product->id])}}">
                                                                                                {{$orderDetail->product->name}}
                                                                                            </a>

                                                                                        </h5>

                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <span>{{show_currency()}}{{short_amount($orderDetail->total_price)}}</span>
                                                                        </td>

                                                                        <td class="text-end">
                                                                            {{get_date_time(@$orderDetail->created_at, 'd M Y')}}
                                                                        </td>
                                                                    </tr>
                                                                @endif

                                                            @endforeach

                                                        </tbody>
                                                    </table>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

					<div class="col-xl-3 col-lg-4">
						<div class="card mb-4">
							<div class="card-header">
								<div class="d-flex align-items-center justify-content-between">
									<div class="d-flex align-items-center gap-3">
										<h4 class="card-title fs-16">
											{{translate("Billing Info")}}
										</h4>
									</div>
								</div>
							</div>

							<div class="card-body">

                                @php
                                   $user = auth_user('web') ;
                                @endphp
								<div class="d-flex align-items-start flex-column gap-4 billing-list ">


                                    @foreach(  @$order->billing_information  as $key=>$address)
                                        <span class="fs-14 d-flex align-items-center gap-3"> <small class="text-muted fs-14">{{k2t($key)}}:</small> {{$address}}</span>
                                    @endforeach



								</div>
							</div>
						</div>

						<div class="card">
							<div class="card-header">
								<div class="d-flex align-items-center justify-content-between">
									<div class="d-flex align-items-center gap-3">
										<h4 class="card-title fs-16">
											{{translate("Payment Details")}}
										</h4>
									</div>
								</div>
							</div>

							<div class="card-body ">
								<div class="d-flex align-items-start flex-column gap-4 billing-list">
									<span class="fs-14 d-flex align-items-center gap-3"> <small class="text-muted fs-14">
									{{translate("Transactions")}}	:</small> {{@$order->order_id}}</span>
									<span class="fs-14 d-flex align-items-center gap-3"> <small class="text-muted fs-14">
									{{translate("Payment Method")}}	:</small>
									   @if($order->payment_type == '2')
									     {{@$order->paymentMethod ? $order->paymentMethod->name : "N/A" }}
										 @else
										    {{translate('Cash On Delivary')}}
									   @endif
								    </span>

									<span class="fs-14 d-flex align-items-center gap-3"> <small class="text-muted fs-14">
									{{translate("Total Amount")}}	:</small> {{show_currency()}}{{short_amount($order->amount)}}
								  </span>

								   <span class="fs-14 d-flex align-items-center gap-3"> <small class="text-muted fs-14">
									{{translate("Payment Status")}}	:</small> @if($order->payment_status == '1' ) {{translate('Unpaid')}} @else {{translate('Paid')}} @endif
								  </span>
								   <span class="fs-14 d-flex align-items-center gap-3"> <small class="text-muted fs-14">
									{{translate("Order Status")}}	:</small>

                                        @if($order->status == App\Models\Order::PLACED)
                                            {{translate('Placed')}}
                                        @elseif($order->status == App\Models\Order::CONFIRMED)
                                                {{translate('Confirmed')}}
                                        @elseif($order->status == App\Models\Order::PROCESSING)
                                            {{translate('Processing')}}
                                        @elseif($order->status == App\Models\Order::SHIPPED)
                                              {{translate('Shipped')}}
                                        @elseif($order->status == App\Models\Order::DELIVERED)
                                             {{translate('Delivered')}}
                                        @elseif($order->status == App\Models\Order::CANCEL)
                                            {{translate('Cancel')}}
                                        @endif



								  </span>

								</div>
							</div>
						</div>
					</div>
				</div>
			@endif
		</div>
	</div>
</section>
@endsection
