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
                        {{translate('Dashboard')}}
                    </a></li>
                    <li class="breadcrumb-item"><a href="{{route('admin.seller.info.index')}}">
                        {{translate('Sellers')}}
                    </a></li>
                    <li class="breadcrumb-item active">
                        {{translate("Shop Details")}}
                    </li>
                </ol>
            </div>
        </div>


		<div class="position-relative mx-n4 mt-n4">
			<div class="profile-wid-bg profile-setting-img">
				<img src="{{show_image(file_path()['shop_first_image']['path'].'/'.@$seller->sellerShop->shop_first_image ,file_path()['shop_first_image']['size'])}}" class="profile-wid-img" alt="{{@$seller->sellerShop->shop_first_image}}">

			</div>
		</div>

		<div class="row">
			<div class="col-xxl-3 col-xl-4">
				<div class="card mt-n5">
					<div class="card-body p-4">
						<div class="text-center">
							<div class="profile-user position-relative d-inline-block mx-auto  mb-4">
								<img src="{{show_image(file_path()['shop_logo']['path'].'/'.@$seller->sellerShop->shop_logo,file_path()['shop_logo']['size'])}}"
									class="rounded-circle avatar-xl img-thumbnail user-profile-image"
									alt="{{@$seller->sellerShop->shop_logo}}">
							</div>
							<div>
								<h6 class="mb-0">{{$seller->name}}</h6>
								<p>{{translate('Joining Date')}} {{get_date_time($seller->created_at,'d M, Y h:i A')}}</p>
							</div>
						</div>

						<div class="p-3 bg-body rounded">
                            <h6 class="mb-3 fw-bold">{{translate('Seller Shop information')}}</h6>

                            <ul class="list-group">
                                <li class="d-flex justify-content-between align-items-center flex-wrap gap-2 list-group-item">
                                    <span class="fw-semibold">
                                        {{translate('name')}}
                                    </span>

                                    <span class="font-weight-bold">{{@$seller->sellerShop->name}}</span>
                                </li>

                                <li class="d-flex justify-content-between align-items-center flex-wrap gap-2 list-group-item">
                                    <span class="fw-semibold">
                                        {{translate('Email')}}
                                    </span>
                                    <span class="font-weight-bold text-break">{{@$seller->sellerShop->email}}</span>
                                </li>

                                <li class="d-flex justify-content-between align-items-center flex-wrap gap-2 list-group-item">
                                    <span class="fw-semibold">
                                        {{translate('Phone')}}
                                    </span>
                                    <span class="font-weight-bold">{{@$seller->sellerShop->phone}}</span>
                                </li>

                                <li class="d-flex justify-content-between align-items-center flex-wrap gap-2 list-group-item">
                                    <span class="fw-semibold">
                                        {{translate('Address')}}
                                    </span>
                                    <span class="font-weight-bold">{{@$seller->sellerShop->address}}</span>
                                </li>

                                <li class="d-flex justify-content-between align-items-center flex-wrap gap-2 list-group-item">
                                    <span class="fw-semibold">{{translate('Status')}} :</span>
                                    @if(@$seller->sellerShop->status == 1)
                                        <span class="badge badge-pill bg-success">{{translate('Active')}}</span>
                                    @else
                                        <span class="badge badge-pill bg-danger">{{translate('Banned')}}</span>
                                    @endif
                                </li>
                            </ul>
						</div>
					</div>
				</div>
			</div>

			<div class="col-xxl-9 col-xl-8">
				<div class="card mt-xxl-n5">

					<div class="card-body p-4">

						<form action="{{route('admin.seller.info.shop.update', $seller->id)}}" method="POST" enctype="multipart/form-data">
							@csrf
							<div class="row">
								<div class="col-lg-12">
									<div class="mb-3">
										<label for="details" class="form-label">
											{{translate("Shop Details")}}
										</label>
										 <textarea class="form-control" disabled name="details" id="details"  cols="30" rows="10">{{(@$seller->sellerShop->short_details)}}</textarea>
									</div>
								</div>
								<div class="col-lg-12">
									<div class="mb-3">
										<label for="status" class="form-label">{{translate('Status')}} <span class="text-danger">*</span></label>
										<select class="form-select" name="status" id="status">
											<option value="1" @if(@$seller->sellerShop->status == 1) selected @endif>{{translate('Approved')}}</option>
											<option value="2" @if(@$seller->sellerShop->status == 2) selected @endif>{{translate('Inactive')}}</option>
										</select>
									</div>
								</div>

								<div class="col-lg-12">
									<div class="hstack gap-2 justify-content-start">
										<button type="submit"
											class="btn btn-success">
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

    </div>
</div>

@endsection
