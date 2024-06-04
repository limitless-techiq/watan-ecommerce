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

@endsection

@push('scriptpush')
    <script>
        'use strict';
        $('#sort').on('change', function(){
            this.form.submit();
        });
    </script>
@endpush
