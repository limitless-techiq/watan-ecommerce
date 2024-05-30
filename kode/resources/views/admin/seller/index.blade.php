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
                    <li class="breadcrumb-item active">
                        {{translate("Seller")}}
                    </li>
                </ol>
            </div>
        </div>

        <div class="card">
            <div class="card-header border-0">
                <div class="d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">
                        {{translate('Seller List')}}
                    </h5>
                </div>
            </div>

            <div class="card-body border border-dashed border-end-0 border-start-0">
                <form action="{{route(Route::currentRouteName(),Route::current()->parameters())}}" method="get">
                    <div class="row g-3">
                        <div class="col-xl-4 col-sm-6">
                            <div class="search-box">
                                <input type="text" name="search" value="{{request()->input('search')}}" class="form-control search"
                                    placeholder="{{translate('Search name,email,phone')}}">
                                <i class="ri-search-line search-icon"></i>
                            </div>
                        </div>


                        <div class="col-xl-2 col-sm-3 col-6">
                            <div>
                                <button type="submit" class="btn btn-primary w-100 waves ripple-light"> <i
                                        class="ri-equalizer-fill me-1 align-bottom"></i>
                                    {{translate('Search')}}
                                </button>
                            </div>
                        </div>

                        <div class="col-xl-2 col-sm-3 col-6">
                            <div>
                                <a href="{{route(Route::currentRouteName(),Route::current()->parameters())}}" class="btn btn-danger w-100 waves ripple-light"> <i
                                        class="ri-refresh-line me-1 align-bottom"></i>
                                    {{translate('Reset')}}
                                </a>
                            </div>
                        </div>

                    </div>
                </form>
            </div>

            <div class="card-body pt-0">
                <ul class="nav nav-tabs nav-tabs-custom nav-primary mb-3" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{request()->routeIs('admin.seller.info.index') ? 'active' :'' }} All py-3"  id="All"
                            href="{{route('admin.seller.info.index')}}" >
                            <i class="ri-group-fill me-1 align-bottom"></i>
                            {{translate('All
                            Seller')}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{request()->routeIs('admin.seller.info.active') ? 'active' :''}}   py-3"  id="Placed"
                            href="{{route('admin.seller.info.active')}}" >
                            <i class="ri-user-star-line me-1 align-bottom"></i>
                            {{translate('Active Seller')}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{request()->routeIs('admin.seller.info.banned') ? 'active' :''}} Confirmed py-3"  id="Confirmed"
                            href="{{route('admin.seller.info.banned')}}" >
                            <i class="ri-forbid-fill me-1 align-bottom"></i>
                            {{translate("Banned Seller")}}

                        </a>
                    </li>
                </ul>

                <div class="table-responsive table-card">
                    <table class="table table-hover table-nowrap align-middle mb-0" >
                        <thead class="text-muted table-light">
                            <tr class="text-uppercase">
                                <th>
                                    {{translate(
                                        "Name - Username"
                                    )}}
                                </th>

                                <th>
                                    {{translate('Email - Phone')}}
                                </th>

                                <th>
                                    {{translate('Best Seller')}}
                                </th>

                                <th  >{{translate('Balance - Total Products')}}
                                </th>

                                <th>
                                    {{translate('Status')}}
                                </th>

                                <th>
                                    {{translate('Shop Status')}}
                                </th>

                                <th >
                                    {{translate('Action')}}
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($sellers as $seller)
                                <tr>
                                    <td data-label="{{translate('Name - Username')}}">
                                        <span class="fw-bold">{{@($seller->name)}}</span> <br>
                                        {{@($seller->username)}}
                                    </td>

                                    <td data-label="{{translate('Email - Phone')}}">
                                        {{@($seller->email)}}<br>
                                        {{@($seller->phone)}}
                                    </td>

                                    <td class="text-start" data-label="{{translate('Best Seller')}}">
                                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="{{translate('Best Seller')}}" href="{{route('admin.seller.info.best.status', $seller->id)}}" class="
                                            link-{{$seller->best_seller_status==1 ? 'danger' :'success'}}
                                            fs-19">
                                            @if($seller->best_seller_status==1)
                                                <i class="fs-18 ri-close-circle-line"></i>
                                            @else
                                                <i class="fs-18 ri-check-fill"></i>
                                            @endif
                                        </a>
                                    </td>


                                    <td data-label="{{translate('Balance - Total Products')}}">
                                            <div>
                                                <span>{{round(($seller->balance))}} {{$general->currency_name}}</span>
                                                <a  data-bs-toggle="tooltip" data-bs-placement="top" title="{{translate('Products')}}" class="ms-2 badge text-bg-primary custom-toggle active"  href="{{route('admin.seller.info.physical.product', $seller->id)}}" >{{translate('View product')}} ({{$seller->product->wherenull('deleted_at')->where('product_type', 102)->count()}})</a>
                                            </div>
                                    </td>

                                    <td data-label="{{translate('Status')}}">
                                        @if($seller->status == 1)
                                            <span class="badge badge-soft-success">{{translate('Active')}}</span>
                                        @else
                                            <span class="badge badge-soft-danger">{{translate('Banned')}}</span>
                                        @endif
                                    </td>

                                    <td data-label="{{translate('Status')}}">
                                        @if(@$seller->sellerShop->status == 1)
                                            <span class="badge badge-soft-success">{{translate('Enable')}}</span>
                                        @else
                                            <span class="badge badge-soft-danger">{{translate('Disable')}}</span>
                                        @endif
                                    </td>
                                    
                                    <td data-label="{{translate('Action')}}">
                                        <div class="hstack justify-content-center gap-3">
                                            <a target="_blank" class="link-success fs-18 " data-bs-toggle="tooltip" data-bs-placement="top" title="Login" href="{{route('admin.seller.info.login', $seller->id)}}"><i class="ri-login-box-line"></i></a>

                                            <a class="link-info fs-18 " data-bs-toggle="tooltip" data-bs-placement="top" title="Details" href="{{route('admin.seller.info.details', $seller->id)}}"><i class="ri-list-check"></i></a>
                                            <a class="link-success  fs-18 " data-bs-toggle="tooltip" data-bs-placement="top" title="Shop"  href="{{route('admin.seller.info.shop', $seller->id)}}"><i class="las la-store-alt"></i></a>
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

                <div class="pagination-wrapper d-flex justify-content-end mt-4">
                    {{$sellers ->appends(request()->all())->links()}}
                </div>
            </div>
        </div>

    </div>
</div>
@endsection


