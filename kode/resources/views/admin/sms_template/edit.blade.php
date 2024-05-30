@extends('admin.layouts.app')
@section('main_content')
<div class="page-content">
	<div class="container-fluid">

        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">
                {{translate('SMS Template')}}
            </h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">
                        {{translate('Home')}}
                    </a></li>
                    <li class="breadcrumb-item"><a href="{{route('admin.sms.template.index')}}">
                        {{translate('Templates')}}
                    </a></li>

                    <li class="breadcrumb-item active">
                        {{translate('Update')}}
                    </li>
                </ol>
            </div>
        </div>


	    <div class="row">
			<div class="col-xl-9">
				<div class="card">
					<div class="card-header border-bottom-dashed">
						<div class="row g-4 align-items-center">
							<div class="col-sm">
								<div>
									<h5 class="card-title mb-0">
										{{translate('Sms Template Short Code')}}
									</h5>
								</div>
							</div>
						</div>
					</div>

					<div class="card-body">
						<form action="{{route('admin.sms.template.update', $smsTemplate->id)}}" method="POST" enctype="multipart/form-data" novalidate="">
							@csrf
							<div class="row g-3 mb-3">
								<div class="col-lg-6">
									<label for="subject" class="form-label">{{translate('Subject')}}<span class="text-danger">*</span></label>
									<input type="text" name="subject" class="form-control" value="{{$smsTemplate->subject}}" placeholder="{{translate('Enter Subject')}}" required>
								</div>

								<div class="col-lg-6">
									<label for="subject" class="form-label">{{translate('Status')}}<span class="text-danger">*</span></label>
									<select class="form-select" name="status" id="status" required>
										<option value="1" @if($smsTemplate->status == 1) selected @endif>{{translate('Active')}}</option>
										<option value="2" @if($smsTemplate->status == 2) selected @endif>{{translate('Inactive')}}</option>
									</select>
								</div>

								<div class="col-12">
									<label for="sms_body" class="form-label">{{translate('Description')}}<span class="text-danger">*</span></label>
									<textarea class="form-control" name="sms_body" rows="5" id="sms_body" required>{{$smsTemplate->sms_body}}</textarea>
								</div>
								<div class="col-12">
									<button type="submit" class="btn btn-success">{{translate('Submit')}}</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>

			<div class="col-xl-3">
				<div class="card">
					<div class="card-header border-bottom-dashed">
						<div class="row g-4 align-items-center">
							<div class="col-sm">
								<div>
									<h5 class="card-title mb-0">
										{{translate('Template')}}
									</h5>
								</div>
							</div>

						</div>
					</div>

					<div class="card-body">
						<div>

                            <h6 class="mb-0">{{translate('Sms Template Short Code')}}</h6>

							<div class="mt-3">
								<div class="text-center d-flex flex-column gap-2">
									@foreach($smsTemplate->codes as $key => $value)
										<div class="p-2 border d-flex  align-items-center justify-content-between">
											<div class="me-2">
												<h6 class="mb-0">{{$value}}</h6>
											</div>
											<p class="mb-0">@php echo "{{". $key ."}}"  @endphp</p>
										</div>
									@endforeach

								</div>
							</div>
						</div>
				    </div>
				</div>
			</div>
		</div>
    </div>
@endsection

