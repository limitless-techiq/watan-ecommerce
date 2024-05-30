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
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                            <h4 class="card-title">
                                {{translate("Ordar Details")}}
                            </h4>
                    </div>
                </div>
            </div>
            <div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-nowrap align-middle">
                            <thead class="table-light">
                                <tr class="text-muted fs-14">

                                    <th scope="col" class="text-start">
                                        {{translate("Product")}}
                                    </th>
                                    <th scope="col" class="text-center">
                                        {{translate("Qty")}}
                                    </th>
                                    <th scope="col" class="text-center">
                                        {{translate("Varient")}}
                                    </th>
                                    <th scope="col" class="text-center">
                                        {{translate("Total Price")}}
                                    </th>
                                    <th scope="col" class="text-center">
                                        {{translate('Status')}}
                                    </th>

                                </tr>
                            </thead>
                            <tbody class="border-bottom-0">
                                @forelse($orderDetails as $orderDetail)
                                    @if($orderDetail->product)
                                        <tr class="fs-14 tr-item" >
                                            <td>
                                                <div class="wishlist-product align-items-center">
                                                    <div class="wishlist-product-img">
                                                        <img src="{{show_image(file_path()['product']['featured']['path'].'/'.$orderDetail->product->featured_image,file_path()['product']['featured']['size'])}}" alt="{{$orderDetail->product->name}}">
                                                    </div>
                                                    <div class="wishlist-product-info">
                                                        <h4 class="product-title">

                                                            <a  href="{{route('product.details',[make_slug($orderDetail->product->name),$orderDetail->product->id])}}">
                                                                {{$orderDetail->product->name}}
                                                            </a>
                                
                                                        </h4>

                                                        <div class="ratting mb-0">
                                                            @php echo show_ratings($orderDetail->product->review->avg('ratings')) @endphp
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="text-center">
                                                {{$orderDetail->quantity}}
                                            </td>
                                            <td class=" text-center">
                                                <span class="badge-soft-dark px-2 py-1 rounded fs-12"> {{$orderDetail->attribute}}</span>
                                            </td>
                                            <td class=" text-center">
                                                {{show_currency()}}{{short_amount($orderDetail->total_price)}}
                                            </td>
                                            <td class=" text-center">

                                                @if($orderDetail->status == 1)
                                                   <span class="badge badge-soft-primary">{{translate('Placed')}}</span>
                                                @elseif($orderDetail->status == 2)
                                                   <span class="badge badge-soft-info" >{{translate('Confirmed')}}</span>
                                                @elseif($orderDetail->status == 3)
                                                   <span class="badge badge-soft-info" >{{translate('Processing')}}</span>
                                                @elseif($orderDetail->status == 4)
                                                   <span class="badge badge-soft-warning" >{{translate('Shipped')}}</span>
                                                @elseif($orderDetail->status == 5)
                                                   <span class="badge badge-soft-success">{{translate('Delivered ')}}</span>
                                                @elseif($orderDetail->status == 6)
                                                   <span class="badge badge-soft-danger">{{translate('Cancel')}}</span>
                                                @endif

                                            </td>

                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td class="text-center py-5" colspan="100">{{translate('No Data Found')}}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                    </div>
                </div>

            </div>

        </div>
    </div>
</section>


@endsection

