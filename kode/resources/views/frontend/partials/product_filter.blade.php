<div class="card sticky-side-div filter-sidebar">
	<div class="card-header px-25 py-15">
		<div class="d-flex">
			<div class="flex-grow-1">
				<h5 class="card-title">
					{{translate('Filter')}}
				</h5>
			</div>
		</div>
	</div>

	<div class="filter-accordion">
		<div class="px-25 py-15 border-bottom">
			<div>
				<p class="text-uppercase fs-13 fw-semibold filter-by">
					{{translate('Category')}}
				</p>
				<ul class="list-unstyled mb-0 mt-3 filter-list">


					@php
			         		$filterCategories = App\Models\Category::whereHas('physicalProduct')->where('status', '1')
						                            	->withCount(['parent','product','houseProduct','digitalProduct','physicalProduct'])
							                            ->whereNull('parent_id')
														->orderBy('serial', 'ASC')
														->with(['physicalProduct'])->get();
						    $filterBrands = App\Models\Brand::with(['product','houseProduct'])
							                            ->withCount('product')->where('status', '1')
														->orderBy('serial', 'ASC')
														->get();

												
					 @endphp
					@forelse($filterCategories as $category)
						<li>
							<a href="{{route('category.product', [make_slug(@get_translation($category->name)), $category->id])}}" class="d-flex cate-menu-active align-items-center position-relative">
								<div class="flex-grow-1">
									<h5 class="listname
								      @if(request()->routeIs('category.product'))
									    {{request()->route('id') == $category->id ? 'cate-menu-active' :''}}
									  @endif">{{@get_translation($category->name)}}</h5>
								</div>

								<span class="flex-shrink-0 ms-2 badge bg-light text-muted fs-12">
									{{
										$category->houseProduct->count()
									}}
								</span>
							</a>
						</li>
					@empty

					   <li>
					     	@include("frontend.partials.empty",['message' => 'No Data Found'])
					   </li>

					@endforelse
				</ul>
			</div>
		</div>

		<div class="p-25 border-bottom">
			<p class="text-uppercase fs-13 fw-semibold mb-2 filter-by">
				{{translate("Filter By Price")}}
			</p>
			<form action="{{route(Route::currentRouteName(),Route::current()->parameters())}}" method="GET">
				<div class="range-slider mb-4">
					@php
						$search_min = request()->input('search_min') ? request()->input('search_min') : round(short_amount($general->search_min));
						$search_max = request()->input('search_max') ?  request()->input('search_max') :  round(short_amount($general->search_max));
					@endphp
					<div class="slider-area">
						<div id="slider-range" class="slider">

						</div>
					</div>
					<div class="formCost d-flex gap-2 align-items-center">
						<input class="form-control form-control-sm" name="search_min" id="skip-value-lower" type="number"  value="{{$search_min}}" min="{{short_amount($general->search_min)}}" max="{{short_amount($general->search_max)}}" />
							<span class="text-muted fs-14">
								{{translate('to')}}
							</span>
						<input
							class="form-control form-control-sm" name="search_max" id="skip-value-upper" type="number" value="{{$search_max}}" min="{{short_amount($general->search_min)}}" max="{{short_amount($general->search_max)}}"/>
					</div>
				</div>

				<button type="submit" class="address-btn wave-btn w-100">
					{{translate('filter')}}
				</button>
		   </form>
		</div>

		<div class="p-25">
			<span class="text-uppercase fs-13 fw-semibold filter-by">
				{{translate('Brands')}}
			</span>
			<div class="d-flex flex-column gap-2 mt-2 filter-check">
				<ul class="list-unstyled mb-0 filter-list">
					@forelse($filterBrands as $brand)
						<li>
							<a href="{{route('brand.product',[make_slug(@get_translation($brand->name)), $brand->id])}}" class="d-flex align-items-center position-relative">
								<div class="flex-grow-1">
									<h5 class="listname @if(request()->routeIs('brand.product'))
										{{request()->route('brand_id') == $brand->id ? 'cate-menu-active' :'' }}
										@endif ">{{(@get_translation($brand->name))}}</h5>
								</div>
								<span class="flex-shrink-0 ms-2 badge bg-light text-muted fs-12">{{($brand->houseProduct->count())}}</span>

							</a>
						</li>

					@empty

						<li>
							  @include("frontend.partials.empty",['message' => 'No Data Found'])
						</li>

					 @endforelse

				</ul>
			</div>
		</div>
	</div>
</div>


@push('scriptpush')
<script>
    'use strict';

	var skipSlider = document.getElementById("slider-range");
	if (skipSlider != null) {
		var skipValues = [
			document.getElementById("skip-value-lower"),
			document.getElementById("skip-value-upper")
		];

		noUiSlider.create(skipSlider, {
			start: [{{$search_min }},{{$search_max}}],
			connect: true,
			behaviour: "drag",
			step: 1,
			range: {
				min: {{round(short_amount($general->search_min))}},
				max: {{round(short_amount($general->search_max))}}
			},
			format: {
				from: function (value) {
					return parseInt(value);
				},
				to: function (value) {
					return parseInt(value);
				}
			}
		});

		skipSlider.noUiSlider.on("update", function (values, handle) {
			skipValues[handle].value = values[handle];
		});
	}

</script>
@endpush
