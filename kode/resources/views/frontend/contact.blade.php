@extends('frontend.layouts.app')
@section('content')
@php
    $contact = frontend_section('contact');
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


<section class="contact-section pb-80">
    <div class="Container">
        <div class="row">
            <div class="col-lg-9">
                <div class="section-title">
                    <div class="title-left-content">
                        <h3>   {{@frontend_section_data($contact->value,'heading')}}</h3>
                        <p> {{@frontend_section_data($contact->value,'sub_heading')}}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-0">
            <div class="col-lg-6 order-lg-1 order-2">
                <div class="form-wrapper">
                    <form action="{{route('contact.store')}}" class="contact-form" method="post">
                        @csrf
                        <div>
                            <label for="name" class="form-label">{{translate('Your Name')}}</label>
                                <input type="text" required  name="name" value="{{old('name')}}" id="name" class="form-control" placeholder="{{translate('Your Name')}}">
                        </div>

                        <div>
                            <label for="email" class="form-label">{{translate('Your Email')}}</label>
                            <input required type="email" class="form-control"  name="email" id="email" value="{{old('email')}}" placeholder="{{translate('Your Email')}}">
                        </div>

                        <div>
                            <label for="subject" class="form-label">{{translate('Subject')}}</label>
                            <input required type="text" class="form-control"  name="subject" id="subject" value="{{old('subject')}}" placeholder="{{translate('Your subject')}}">
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">{{translate(' Message')}} </label>
                            <textarea required id="message" name="message" rows="4" class="form-control" placeholder="{{translate('Your Message Here')}}">{{old('message')}}</textarea>
                        </div>
                        <input type="submit" value="Send" class="form-submit-btn">
                    </form>
                </div>
            </div>
            <div class="col-lg-6 order-lg-2 order-1">
                <div class="form-image-wrapper img-adjus contact-bg" >
                    <ul class="contact-list">
                        <li>
                            <div class="icon">
                                <i class="las la-envelope"></i>
                            </div>
                            <div class="content">
                                <span>{{translate("Eamil")}}</span>
                                <a href="mailto:{{$general->mail_from}}">
                                     {{$general->mail_from}}
                                </a>
                            </div>
                        </li>
                        <li>
                            <div class="icon">
                                <i class="las la-phone-volume"></i>
                            </div>
                            <div class="content">
                                <span>{{translate('Phone')}}</span>
                                <a href="tel:{{$general->phone}}">{{$general->phone}}</a>
                            </div>
                        </li>
                        <li>
                            <div class="icon">
                                <i class="las la-map-marker"></i>
                            </div>
                            <div class="content">
                                <span>{{translate('Address')}}</span>

                                 <p>{{$general->address}}</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
