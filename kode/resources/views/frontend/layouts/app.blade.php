<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('frontend.partials.seo')
    <title>{{($general->site_name)}} - {{($title)}}</title>
    <link rel="shortcut icon" href="{{show_image('assets/images/backend/logoIcon/'.$general->site_favicon,file_path()['favicon']['size'])}}" type="image/x-icon">

    <link href="{{asset('assets/frontend/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" />

    <link href="{{asset('assets/frontend/css/swiper-bundle.min.css')}}" rel="stylesheet" type="text/css">
    <link href="{{asset('assets/frontend/css/all.min.css')}}" rel="stylesheet" type="text/css" />

    <link rel="stylesheet" href="{{asset('assets/frontend/css/line-awesome.min.css')}}" />

    <link href="{{asset('assets/frontend/css/nouislider.min.css')}}" rel="stylesheet" type="text/css">
    <link href="{{asset('assets/frontend/css/global.css')}}" rel="stylesheet" type="text/css">
    <link href="{{asset('assets/frontend/css/custom.css')}}" rel="stylesheet" type="text/css">
    <link href="{{asset('assets/frontend/css/view-ticket.css')}}" rel="stylesheet" type="text/css">
    <link href="{{asset('assets/frontend/css/bootstrap-custom.css')}}" rel="stylesheet" type="text/css">
    <link href="{{asset('assets/frontend/css/style.css')}}" rel="stylesheet" type="text/css">
    <link href="{{asset('assets/global/css/toastr.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/frontend/css/media.css')}}" rel="stylesheet" type="text/css">

    @include('frontend.partials.color')
    @stack('style-include')
    @stack('stylepush')

    <style>

        .newsletter {
            position: relative;
            background: linear-gradient(45deg, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.6)),
                url("{{asset('assets/images/news_latter.jpg')}}");
            z-index: 1;
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
        }

        .login-bg{
            background-size: cover !important;
            background-repeat: no-repeat !important;
            background-position: center center !important;
            z-index: 2 !important;
           background: url("{{asset('assets/images/login_bg.jpg')}}");
        }

        .contact-bg{
            background-size: cover !important;
            background-repeat: no-repeat !important;
            background-position: center center !important;
            z-index: 2 !important;
            background: url("{{asset('assets/images/contact.jpg')}}");
        }


        .footer {
                background: linear-gradient(
                    90deg,
                    rgba(255, 255, 255, 0.5),
                    rgba(255, 255, 255, 0.5)
                    ),
                    url("{{asset('assets/images/footer_bg.jpg')}}");
                position: relative;
                z-index: 1;
                background-size: cover;
                background-repeat: no-repeat;
                background-position: center;
        }

        .testimonial-section {

            background: linear-gradient(90deg, var(--primary-light), var(--primary-light)),
                  url("{{asset('assets/images/testimonial.jpg')}}");
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center center;
        }


        .title-left-content > h3::after {
            content: url("{{asset('assets/images/title-vector.png')}}");
            position: absolute;
            right: 0px;
            display: inline-block;
            top: 12px;
            width: 50px !important;
            height: 30px;
            z-index: -1;
        }

    </style>
</head>

<body>

    @if($general->preloader == 1)

        <div class="preloader-wrapper">
            <canvas id="bg"></canvas>
            <div class="loader-content">
                <div class="loader-img scale-up-center">
                    <img src="{{ show_image('assets/images/backend/logoIcon/'.$general->site_logo, file_path()['site_logo']['size']) }}" alt="site-logo.jpg" class="img-fluid">
                </div>
            </div>
        </div>

    @endif

    @php
            $support = frontend_section('support');
            $contact = frontend_section('contact');
            $login = frontend_section('login');
            $apps_section =  frontend_section('app-section');
            $footer_text =  frontend_section('footer-text');
            $social_icon =  frontend_section('social-icon');
            $paymnet_image =  frontend_section('payment-image');
            $cookie = frontend_section('cookie');
            $news_latter = frontend_section('news-latter');
            $tawkTo =  json_decode($general->tawk_to,true);


            $search_min = session()->get('search_min') ? session()->get('search_min') : round(short_amount($general->search_min));
                                $search_max = session()->get('search_max') ?  session()->get('search_max') :  round(short_amount($general->search_max));
    @endphp
    @include('frontend.partials.header')

    @if(!session()->has('dont_show'))
        @includeWhen($news_latter->status == '1','frontend.partials.newsLatter')
    @endif

    <main>
        @yield('content')

        @includeWhen($news_latter->status == '1', 'frontend.section.news_later', ['news_latter' => $news_latter])

    </main>
        @include('frontend.partials.footer')
        @include('frontend.partials.sidebar')

     @if( $cookie->status == '1'  && !session()->has('cookie_consent') )
         @include('frontend.partials.cookie')
     @endif

     <script src="{{asset('assets/global/js/jquery.min.js')}}"></script>
     <script src="{{asset('assets/global/js/bootstrap.bundle.min.js')}}"></script>
     <script src="{{asset('assets/frontend/js/swiper-bundle.min.js')}}"></script>
     <script src="{{asset('assets/frontend/js/nouislider.min.js')}}"></script>
     <script src="{{asset('assets/global/js/toastify-js.js')}}"></script>
     <script src="{{asset('assets/global/js/helper.js')}}"></script>
     <script src="{{asset('assets/frontend/js/script.js')}}"></script>

     @stack('script-include')
     @include('partials.notify')
     @include('frontend.partials.script')
     @stack('scriptpush')
     <script>
        "use strict";

        $(document).on('click','.product-gallery-small-img',function(e){

            var src = $(this).find('img').attr('src')
            $('.qv-lg-image').attr('src',src)
            $('.magnifier').css("background-image", "url(" + src + ")");
        })
        $(document).on('click','.quick-view-img',function(e){

            var src = $(this).find('img').attr('src')
            $('.qv-lg-image').attr('src',src)

        })

        @if(isset($tawkTo['widget_id']))
                var status = "{{ $tawkTo['status']}}"
                var widget_id = "{{ $tawkTo['widget_id']}}"
                var property_id = "{{ $tawkTo['property_id']}}"

                if(status == '1')
                {
                    var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
                    (function(){

                        var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
                        s1.async=true;
                        s1.src=`https://embed.tawk.to/${property_id}/${widget_id}`;
                        s1.charset='UTF-8';
                        s1.setAttribute('crossorigin','*');
                        s0.parentNode.insertBefore(s1,s0);
                    })();
                }

        @endif

        var newsLetterModal = document.getElementById('newsletterModal');
        var currentRoute  = "{{Route::currentRouteName()}}"
        if (newsLetterModal != null && currentRoute == 'home') {
            window.addEventListener("load", () => {
                var newModal = new bootstrap.Modal(document.getElementById('newsletterModal'), {});
                setTimeout(() => {
                    newModal.show();
                }, 3000)
            })
        }

         $(function(){
             $(document).on('click', '.modal-closer',function(e){
                 e.preventDefault();
                 var data = $('#dont_show').val();
                 var check = $('#dont_show').is(':checked');
                 if(check){
                     $.ajax({
                         headers: {"X-CSRF-TOKEN": "{{csrf_token()}}"},
                         type: "POST",
                         url: "{{route('newslatter.close')}}",
                         dataType: "json",
                         data: {data,  "_token" :"{{csrf_token()}}"},
                   
                     });
                 }
             })
             $(document).on('click', '.update_qty',function(e) {
                    var cartItemQuantity = $('#quantity').val();
                    if ($(this).hasClass('increment')) {
                        cartItemQuantity++;
                    } else {
                        if (cartItemQuantity > 1) {
                            cartItemQuantity--;
                        } else {
                            cartItemQuantity = 1;
                        }
                    }

                    $('#quantity').val(cartItemQuantity);
                });
             })

            $(document).on('click','.oder-btn',function(e){
                $(this).html(`<i class="fa-solid fa-cart-shopping label-icon align-middle fs-14 ">
                             </i>
                                <div class="spinner-border  order-spinner me-1 " role="status">
                                    <span class="visually-hidden"></span>
                                </div>`);

            });


            $(document).on('click','.attribute-select',function(e){
                    var form = $('.quick-view-form')[0]
                    var data = new FormData(form);
                    $.ajax({
                            headers: {"X-CSRF-TOKEN": "{{ csrf_token() }}",},
                            url: "{{route('product.stock.price')}}",
                            method: "post",
                            data: data,
                            processData: false,
                            contentType: false,
                            success: function (response) {
                                var response_data = JSON.parse(response);
                                const currencySymbol = '{{show_currency()}}';
                                const price = response_data.discount_price === 0 ? response_data.price : response_data.discount_price;
                                const priceHtml = `<span>${currencySymbol}${price}</span>`;
                                const discountHtml = response_data.discount_price !== 0 ? ` <del>${currencySymbol}${response_data.price}</del>` : '';
                                const html = priceHtml + discountHtml;

                                var stockHtml  = `<div class="${response_data.stock ? "instock" :"outstock"}">
                                                           <i class="${response_data.stock ? "fa-solid fa-circle-check"  :"fas fa-times-circle"}"></i>
                                                        <p>
                                                            ${response_data.stock ? 'In Stock': 'Stock out' }
                                                        </p>
                                                    </div>`;


                                $('#quick-view-stock').html(stockHtml)

                                $('.price-section').html(html)
                            }
                        });


            })


               // cookie configuration
      $(document).on('click','.cookie-control',function(e){

            $('.js-cookie-consent').hide();
            $.ajax({
                method:'get',
                url: "{{route('accept.cookie')}}",
                dataType: 'json',

                success: function(response){

                    toaster(response.message,'success')

                },
                error: function (error){
                    if(error && error.responseJSON){
                        if(error.responseJSON.errors){
                            for (let i in error.responseJSON.errors) {
                                toaster(error.responseJSON.errors[i][0],'danger')
                            }
                        }
                        else{
                            if((error.responseJSON.message)){
                                toaster(error.responseJSON.message,'danger')
                            }
                            else{
                                toaster( error.responseJSON.error,'danger')
                            }
                        }
                    }
                    else{
                        toaster(error.message,'danger')
                    }
                }
            })
        })




        </script>


</body>

</html>
