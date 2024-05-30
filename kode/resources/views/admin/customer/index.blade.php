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
                        {{translate("Customers")}}
                    </li>
                </ol>
            </div>

        </div>

        <div class="card">
            <div class="card-header border-0">
                <div class="d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">
                        {{translate('Customer List')}}
                    </h5>
                </div>
            </div>

            <div class="card-body border border-dashed border-end-0 border-start-0">
                <form action="{{route(Route::currentRouteName(),Route::current()->parameters())}}" method="get">
                    <div class="row g-3">
                        <div class="col-xl-4 col-sm-6">
                            <div class="search-box">
                                <input type="text" name="search" class="form-control search"
                                    placeholder="{{translate('Search by name , email ,username or phone')}}">
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
                                <a href="{{route(Route::currentRouteName(),Route::current()->parameters())}}" class="btn btn-danger w-100 waves ripple-light"
                                    > <i
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
                        <a class="nav-link {{request()->routeIs('admin.customer.index') ? 'active' :'' }} All py-3"  id="All"
                            href="{{route('admin.customer.index')}}" >
                            <i class="ri-group-fill me-1 align-bottom"></i>
                            {{translate('All
                            Customer')}}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{request()->routeIs('admin.customer.active') ? 'active' :''}}   py-3"  id="Placed"
                            href="{{route('admin.customer.active')}}" >

                            <i class="ri-user-follow-line me-1 align-bottom"></i>
                            {{translate('Active Customer')}}

                        </a>
                    </li>
                    <li class="nav-item">
                        <a class='nav-link {{request()->routeIs("admin.customer.banned") ? "active" :""}} Confirmed py-3'  id="Confirmed"
                            href="{{route('admin.customer.banned')}}" >

                            <i class="ri-user-unfollow-line me-1 align-bottom"></i>
                            {{translate("Banned Customer")}}

                        </a>
                    </li>
                </ul>

                <div class="table-responsive table-card">
                    <table class="table table-hover table-nowrap align-middle mb-0" >
                        <thead class="text-muted table-light">
                            <tr class="text-uppercase">
                                <th>{{translate('Customer - Username')}}</th>
                                <th>{{translate('Email - Phone')}}</th>
                                <th>{{translate('Number of Orders')}}</th>
                                <th>{{translate('Status')}}</th>
                                <th>{{translate('Joined At')}}</th>
                                <th>{{translate('Action')}}</th>
                            </tr>
                        </thead>

                        <tbody class="list form-check-all">
                            @forelse($customers as $customer)
                                <tr>
                                    <td data-label='{{translate("Customer - Username")}}'>
                                        <span class="fw-bold">
                                            {{($customer->name ?? 'N/A')}}
                                        </span>
                                            <br>
                                        {{($customer->username ?? 'N/A')}}
                                    </td>

                                    <td data-label="{{translate('Email - Phone')}}">
                                        {{($customer->email)}}<br>
                                        {{($customer->phone ?? 'N/A')}}
                                    </td>

                                    <td class="text-start" data-label="{{translate('Number of Orders')}}">
                                        {{$customer->order->count()}}
                                    </td>

                                    <td data-label="{{translate('Status')}}">

                                        @if($customer->status == 1)
                                           <span class="badge badge-soft-success">{{translate('Active')}}</span>
                                        @else
                                            <span class="badge badge-soft-danger">{{translate('Banned')}}</span>
                                        @endif
                                    </td>

                                    <td data-label="{{translate('Joined At')}}">
                                        {{diff_for_humans($customer->created_at)}}<br>
                                        {{get_date_time($customer->created_at)}}
                                    </td>

                                    <td data-label="{{translate('Action')}}">
                                        <div class="hstack justify-content-center gap-3">

                                            <a target="_blank" class="link-success fs-18 " data-bs-toggle="tooltip" data-bs-placement="top" title="Login" href="{{route('admin.customer.login', $customer->id)}}"><i class="ri-login-box-line"></i></a>

                                            <a class="link-info fs-18 " data-bs-toggle="tooltip" data-bs-placement="top" title="Details"  href="{{route('admin.customer.details', $customer->id)}}"><i class="ri-list-check"></i></a>
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

                <div class="pagination-wrapper d-flex justify-content-end mt-4 ">
                    {{$customers ->appends(request()->all())->links()}}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
