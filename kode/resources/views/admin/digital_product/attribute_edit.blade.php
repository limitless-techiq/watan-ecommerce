
@extends('admin.layouts.app')
@section('main_content')
<div class="page-content">
    <div class="container-fluid">

        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">
                {{$title}}
            </h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">
                        {{translate('Home')}}
                    </a></li>
                    <li class="breadcrumb-item"><a href="{{route('admin.digital.product.index')}}">
                        {{translate('Digital Products')}}
                    </a></li>
                    <li class="breadcrumb-item active">
                        {{translate("Attributes Values")}}
                    </li>
                </ol>
            </div>
        </div>


        <div class="row">
            <div class="col-md-8 mx-auto">
				<div class="card">
					<div class="card-header border-bottom-dashed">
						<div class="row g-4 align-items-center">
							<div class="col-sm">
								<div>
									<h5 class="card-title mb-0">
										{{translate('Add Values')}}
									</h5>
								</div>
							</div>
						</div>
					</div>
					<div class="card-body">
						<div class="p-2">
							<form action="{{route('admin.digital.product.attribute.update', $digitalProductAttribute->id)}}" method="POST" enctype="multipart/form-data">
								@csrf
								<div>
									<div class="row">

										<div class="mb-3 col-lg-12 col-md-12">
											<label for="text" class="form-label">{{translate('Values')}} <span class="text-danger">*</span></label>
											<textarea required class="form-control" name="text" id="text" placeholder="{{translate('Text format e.g key1,key2,key3')}}"></textarea>
										</div>
									</div>
								</div>
								<button type="submit" class="btn btn-md btn-success">{{translate('Submit')}}</button>
							
							</form>
						</div>
					</div>
				</div>

                <div class="card" id="orderList">
					<div class="card-header border-bottom-dashed">
						<div class="row g-4 align-items-center">
							<div class="col-sm">
								<div>
									<h5 class="card-title mb-0">
										{{translate('Attribute Values')}}
									</h5>
								</div>
							</div>
						</div>
					</div>

                    <div class="card-body pt-2">
                            <div class="table-responsive table-card">
                                <table class="table table-nowrap align-middle" >
                                    <thead class="text-muted table-light">
                                        <tr class="text-uppercase">

											<th>{{translate('Value')}}</th>
											<th>{{translate('Status')}}</th>
											<th>{{translate('Action')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list form-check-all">
										@forelse($digitalProductAttributeValues as $digitalProductAttributeValue)
											<tr class="@if($loop->even) table-light @endif">
													<td data-label="{{translate('Value')}}">
														{{($digitalProductAttributeValue->value)}}
													</td>
													<td data-label="{{translate('Status')}}">
														@if($digitalProductAttributeValue->status == '1')
															<span class="badge badge-soft-success">{{translate('Active')}}</span>
														@else
															<span class="badge badge-soft-danger">{{translate('Sold')}}</span>
														@endif
													</td>
													<td data-label="{[translate('Action')}}">

														<div class="hstack justify-content-center gap-3">
															<a  title="{{translate('Delete')}}" data-bs-toggle="tooltip" data-bs-placement="top" href="javascript:void(0)" class="link-danger fs-18 attributedelete" data-bs-toggle="modal" data-id="{{$digitalProductAttributeValue->id}}" data-bs-target="#delete"><i class="las la-trash"></i></a>
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
                                {{ $digitalProductAttributeValues->links() }}
                            </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="delete" tabindex="-1" aria-labelledby="delete" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        	<form action="{{route('admin.digital.product.attribute.delete')}}" method="POST">
        		@csrf
        		<input type="hidden" name="id">
				<div class="modal-body">
					<div class="mt-2 text-center">
						<lord-icon src="{{asset('assets/global/gsqxdxog.json')}}" trigger="loop"
							colors="primary:#f7b84b,secondary:#f06548"
							class="loader-icon"

							></lord-icon>
						<div class="mt-4 pt-2 fs-15 mx-4 mx-sm-5">
							<h4>
							{{translate('Are you sure ?')}}
							</h4>
							<p class="text-muted mx-4 mb-0">
								{{translate('Are you sure you want to
								remove this record ?')}}
							</p>
						</div>
					</div>
				</div>
	            <div class="modal-footer">
	                <button type="button" class="btn btn-md btn-danger" data-bs-dismiss="modal">{{translate('Cancel')}}</button>
	                <button type="submit" class="btn btn-md btn-success">{{translate('Delete')}}</button>
	            </div>
	        </form>
        </div>
    </div>
</div>



@endsection

@push('script-push')
<script>
	(function($){
       	"use strict";
		$(".attributedelete").on("click", function(){
			var modal = $("#delete");
			modal.find('input[name=id]').val($(this).data('id'));
			modal.modal('show');
		});
	})(jQuery);
</script>
@endpush






