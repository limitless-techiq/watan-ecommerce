@extends('frontend.layouts.app')
@section('content')
   @php
     $promo_banner = frontend_section('promotional-offer');
   @endphp

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
        <div class="row g-4">
            @include('user.partials.dashboard_sidebar')

            <div class="col-xl-9 col-lg-8">
                <div class="profile-user-right">
                    <a href="{{@frontend_section_data($promo_banner->value,'image','url')}}" class="d-block">
                        <img class="w-100" src="{{show_image(file_path()['frontend']['path'].'/'.@frontend_section_data($promo_banner->value,'image'),@frontend_section_data($promo_banner->value,'image','size'))}}" alt="banner.jpg">
                    </a>

                    <div class="card mt-5">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <h4 class="card-title">
                                        {{translate("Profile Info")}}
                                    </h4>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <form action="{{route('user.profile.update')}}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label for="profileName" class="form-label">
                                            {{translate("Name")}} <span class="text-danger">*</span>
                                        </label>
                                        <input class="form-control" type="text" name="name" id="profileName" value="{{$user->name}}" placeholder="{{translate('Enter Name')}}" required="">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="last_name" class="form-label">
                                            {{translate("Last Name")}} <span class="text-danger">*</span>
                                        </label>
                                        <input class="form-control" type="text" name="last_name" id="last_name" value="{{$user->last_name}}" placeholder="{{translate('Enter Last Name')}}" required="">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="userName" class="form-label">
                                            {{translate("Username")}} <span class="text-danger">*</span>
                                        </label>
                                        <input class="form-control" type="text" name="username" id="userName" value="{{$user->username}}" placeholder="{{translate('Enter User Name')}}" required="">
                                    </div>

                                    <div class="col-md-6">

                                        <label for="phone" class="form-label">
                                            {{translate("Phone")}} <span class="text-danger">*</span>
                                        </label>
                                        <input class="form-control" type="text" name="phone" id="phone" value="{{$user->phone}}" placeholder="{{translate('Enter Phone')}}" required="">

                                    </div>

                                    <div class="col-md-6">

                                        <label for="address" class="form-label">
                                            {{translate("Address")}} <span class="text-danger">*</span>
                                        </label>
                                        <input class="form-control" type="text" name="address" id="address" value="{{@$user->address->address}}" placeholder="{{translate('Enter Address')}}" required="">

                                    </div>

                                    <div class="col-md-6">
                                        <label for="city" class="form-label">
                                            {{translate("City")}} <span class="text-danger">*</span>
                                        </label>
                                        <input class="form-control" type="text" name="city" id="city" value="{{@$user->address->city}}" placeholder="{{translate('Enter City')}}" required="">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="state" class="form-label">
                                            {{translate("State")}} <span class="text-danger">*</span>
                                        </label>
                                        <input class="form-control" type="text" name="state" id="state" value="{{@$user->address->state}}" placeholder="{{translate('Enter State')}}" required="">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="zip" class="form-label">
                                            {{translate("Zip")}} <span class="text-danger">*</span>
                                        </label>
                                        <input class="form-control" type="text" name="zip" id="zip" value="{{@$user->address->zip}}" placeholder="{{translate('Enter Zip')}}" required="">
                                    </div>

                                    <div class="col-md-12">
                                        <label for="file" class="form-label">
                                            {{translate("Upload Image")}}
                                        </label>
                                        <input class="form-control" type="file" name="image" id="file">
                                    </div>
                                </div>

                                <div class="mt-5">
                                    <button type="submit" class="all-campaign-btn">
                                        {{translate("Save Change")}}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card mt-5">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <h4 class="card-title">
                                        {{translate("Update Password")}}
                                    </h4>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <form action="{{route('user.password.update')}}" method="post" enctype="multipart/form-data">
                                @csrf
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label for="current_password" class="form-label">
                                                {{translate("Current Password")}} 
                                            </label>
                                            <input class="form-control" type="text" name="current_password" id="current_password"  placeholder="{{translate('Enter Current Password')}}" >
                                        </div>

                                        <div class="col-md-6">
                                            <label for="password" class="form-label">
                                                {{translate("New Password")}} <span class="text-danger">*</span>
                                            </label>
                                            <input class="form-control" type="text" name="password" id="password"  placeholder="{{translate('Enter New Password')}}" required="">
                                        </div>

                                        <div class="col-md-6">
                                            <label for="password_confirmation" class="form-label">
                                                {{translate("Confirm Password")}} <span class="text-danger">*</span>
                                            </label>
                                            <input class="form-control" type="text" name="password_confirmation" id="password_confirmation"  placeholder="{{translate('Enter Confirm Password')}}" required="">
                                        </div>
                                    </div>

                                    <div class=" mt-5">
                                        <button type="submit" class="all-campaign-btn">
                                            {{translate("Update")}}
                                        </button>
                                    </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>



@endsection



