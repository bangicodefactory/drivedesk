<!DOCTYPE html>
<html lang="en">
@php
    $settings=settings();
     $user=\App\Models\User::find(1);
    \App::setLocale($user->lang);
@endphp

<head>
    <!-- Required meta tags-->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{env('APP_NAME')}}</title>

    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>

    <meta name="author" content="{{!empty($settings['app_name'])?$settings['app_name']:env('APP_NAME')}}">
    <meta name="csrf-token" content="{{csrf_token()}}">
    <title>{{!empty($settings['app_name'])?$settings['app_name']:env('APP_NAME')}} - @yield('page-title') </title>

    <meta name="title" content="{{$settings['meta_seo_title']}}">
    <meta name="keywords" content="{{$settings['meta_seo_keyword']}}">
    <meta name="description" content="{{$settings['meta_seo_description']}}">


    <meta property="og:type" content="website">
    <meta property="og:url" content="{{env('APP_URL')}}">
    <meta property="og:title" content="{{$settings['meta_seo_title']}}">
    <meta property="og:description" content="{{$settings['meta_seo_description']}}">
    <meta property="og:image" content="{{asset(Storage::url('upload/seo')).'/'.$settings['meta_seo_image']}}">

    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{env('APP_URL')}}">
    <meta property="twitter:title" content="{{$settings['meta_seo_title']}}">
    <meta property="twitter:description" content="{{$settings['meta_seo_description']}}">
    <meta property="twitter:image" content="{{asset(Storage::url('upload/seo')).'/'.$settings['meta_seo_image']}}">

    <!-- shortcut icon-->
    <link rel="icon" href="{{asset(Storage::url('upload/logo')).'/'.$settings['company_favicon']}}" type="image/x-icon">
    <link rel="shortcut icon" href="{{asset(Storage::url('upload/logo')).'/'.$settings['company_favicon']}}"
          type="image/x-icon">

    <!-- shortcut icon-->
    <link rel="shortcut icon" href="{{asset(Storage::url('upload/logo')).'/favicon.png'}}" type="image/x-icon">
    <!-- Fonts css-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
        rel="stylesheet">
    <!-- Font awesome -->
    <link href="{{ asset('assets/css/vendor/font-awesome.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/vendor/icoicon/icoicon.css') }}" rel="stylesheet">

    <link href="{{ asset('assets/css/vendor/slider/slick-slider/slick.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/vendor/slider/slick-slider/slick-theme.css') }}" rel="stylesheet">
    <!-- animat css-->
    <link href="{{ asset('assets/css/vendor/animate.css') }}" rel="stylesheet">
    <!-- Bootstrap css-->
    <link href="{{ asset('assets/css/vendor/bootstrap.css') }}" rel="stylesheet">
    <!-- Custom css-->

    @php
        $style=$settings['theme_color']=='color1'?'style.css':$settings['theme_color'].'.css';
        if($settings['color_type']=='custom'){
            $style='style.css';
        }
    @endphp
    <link href="{{ asset('assets/css/'.$style) }}" id="customstyle" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
</head>

<body class="{{$settings['layout_direction']}} {{$settings['layout_mode']}}">
<!-- header start-->
<header class="land-header fixed">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="header-contain position-relative">
                    <div class="codex-brand">
                        <a href="javascript:void(0);">
                            <img class="img-fluid dark-logo landing-logo"
                                 src="{{asset(Storage::url('upload/logo')).'/'.$settings['landing_logo']}}"
                                 alt="">
                        </a>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="menu-block">
                            <ul class="menu-list">
                                <li class="d-xl-none">
                                    <a class="close-menu" href="javascript:void(0);">
                                        <div class="menu-brand">
                                            <img class="img-fluid" src="{{ asset('assets/images/logo/logo.png') }}"
                                                 alt=""><i data-feather="x"></i>
                                        </div>
                                    </a>
                                </li>
                                <li class="menu-item"><a href="#demos">{{__('Home')}}</a></li>
                                <li class="menu-item"><a href="#pricing">{{__('Pricing')}}</a></li>
                                <li class="menu-item"><a href="#features">{{__('Features')}}</a></li>
                                <li class="menu-item"><a href="#faq">{{__('FAQs')}}</a></li>

                                <li class="menu-item">
                                    <a class="btn btn-primary me-2" href="{{route('login')}}">{{__('Login')}} </a>
                                    @if($settings['register_page']=='on')
                                        <a class="btn btn-primary" href="{{route('register')}}">{{__('Register')}} </a>
                                    @endif

                                </li>

                            </ul>
                            <a class="menu-action d-xl-none" href="javascript:void(0);"><i class="fa fa-bars"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
<!-- header end-->

<!-- intro start-->
<section class="intro" id="demos">
    <div class="container">
        <div class="row">
            <div class="col-xl-6 col-lg-7 col-lg-7">
                <div class="intro-contain">
                    <div>
                        <h1 class="wow fadeInLeft"
                            data-wow-duration="1s">{{__('Car Rental Management System')}}</h1>
                        <p class="wow fadeInLeft"
                           data-wow-duration="1.5s">{{__('A Car Rental System is a comprehensive software solution designed to facilitate the management and operation of a car rental business. ')}}</p>
                        <a class="btn btn-primary" href="{{route('login')}}" data-wow-duration="1.8s"><i
                                class="fa fa-television" aria-hidden="true"></i>{{__('Get Started')}} </a>
                    </div>
                </div>
            </div>

        </div>

    </div>
    <div class="landing_dash">
        <img class="" src="{{ asset('assets/images/landing/1.png') }}" alt="">
    </div>
</section>
<!-- intro end-->
<!-- demo start-->
<section class="space-py-100">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
                <div class="land-title">
                    <h2 class="wow fadeInLeft">{{__('Our Benefits')}}</h2>
                    <h1 class="wow fadeInRight">{{__('Reason to Choose US')}}</h1>
                </div>
            </div>
        </div>
        <div class="row cdx-row justify-content-center">
            <div class="ecompro-slide arrow-style1">
                <div>
                    <div class="card ecom-product">
                        <div class="card-body p-10">
                            <div class="product-imgwrap"><img class="img-fluid"
                                                              src="{{ asset('assets/images/landing/1.png') }}"
                                                              alt="1.jpg"></div>
                            <div class="detail-wrap">
                                <a href="#">
                                    <h5>{{__('Dashboard')}}</h5>
                                    <p>{{__('A Car Rental System is a comprehensive software solution designed to facilitate the management and operation of a car rental business. ')}}</p>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="card ecom-product">
                        <div class="card-body p-10">
                            <div class="product-imgwrap"><img class="img-fluid"
                                                              src="{{ asset('assets/images/landing/2.png') }}"
                                                              alt="1.jpg"></div>
                            <div class="detail-wrap">
                                <a href="#">
                                    <h5>{{__('Cars')}}</h5>
                                    <p>{{__('A Car Rental System is a comprehensive software solution designed to facilitate the management and operation of a car rental business. ')}}</p>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="card ecom-product">
                        <div class="card-body p-10">
                            <div class="product-imgwrap"><img class="img-fluid"
                                                              src="{{ asset('assets/images/landing/3.png') }}"
                                                              alt="1.jpg"></div>
                            <div class="detail-wrap">
                                <a href="#">
                                    <h5>{{__('Booking')}}</h5>
                                    <p>{{__('A Car Rental System is a comprehensive software solution designed to facilitate the management and operation of a car rental business. ')}}</p>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="card ecom-product">
                        <div class="card-body p-10">
                            <div class="product-imgwrap"><img class="img-fluid"
                                                              src="{{ asset('assets/images/landing/4.png') }}"
                                                              alt="1.jpg"></div>
                            <div class="detail-wrap">
                                <a href="#">
                                    <h5>{{__('Planning')}}</h5>
                                    <p>{{__('A Car Rental System is a comprehensive software solution designed to facilitate the management and operation of a car rental business. ')}}</p>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="card ecom-product">
                        <div class="card-body p-10">
                            <div class="product-imgwrap"><img class="img-fluid"
                                                              src="{{ asset('assets/images/landing/5.png') }}"
                                                              alt="1.jpg"></div>
                            <div class="detail-wrap">
                                <a href="#">
                                    <h5>{{__('Rental Agreement')}}</h5>
                                    <p>{{__('A Car Rental System is a comprehensive software solution designed to facilitate the management and operation of a car rental business. ')}}</p>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
<!-- demo end-->
<!-- header otpion start-->
<section class="landheader-comp space-py-100 overflow-visible" id="pricing">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
                <div class="land-title">
                    <h2 class="wow fadeInLeft">{{__('Affordable Pricing Based On Your Needs')}}</h2>
                </div>
            </div>
        </div>
        <div class="row pricing-grid">
            @foreach($subscriptions as $subscription)
                <div class="col-xxl-3 cdx-xl-50 col-sm-6">
                    <div class="codex-pricingtbl">
                        <div class="price-header">
                            <h2>{{$subscription->title}}</h2>
                            <div class="price-value">{{subscriptionPaymentSettings()['CURRENCY_SYMBOL'].$subscription->package_amount}}<span>/ {{$subscription->interval}}</span></div>
                        </div>
                        <ul class="cdxprice-list">
                            <li><span> <i class="text-success mr-4" data-feather="check-circle"></i>{{$subscription->user_limit}}</span>{{__('User Limit')}}</li>
                            <li><span> <i class="text-success mr-4" data-feather="check-circle"></i>{{$subscription->driver_limit}}</span>{{__('Driver Limit')}}</li>
                            <li>
                                @if($subscription->couponCheck()>0)
                                    <i class="text-success mr-4" data-feather="check-circle"></i>
                                @else
                                    <i class="text-danger mr-4" data-feather="x-circle"></i>
                                @endif
                                {{__('Coupon Applicable')}}
                            </li>
                            <li>
                                @if($subscription->enabled_logged_history==1)
                                    <i class="text-success mr-4" data-feather="check-circle"></i>
                                @else
                                    <i class="text-danger mr-4" data-feather="x-circle"></i>
                                @endif
                                {{__('User Logged History')}}
                            </li>
                        </ul>
                        <a class="btn btn-primary" href="{{route('register')}}">{{__('Purchase Now')}} </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
<!-- header otpion End-->
<!-- innderpages start-->
<section class="space-py-100" id="features">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
                <div class="land-title">
                    <h2 class="wow fadeInUp" data-wow-duration="1s">{{__('Features')}} </h2>
                </div>
            </div>
        </div>
        <div class="row cdx-row">
            <div class="col-lg-4 col-md-6 wow fadeInUp">
                <div class="demo-grid">
                    <div class="img-wrap"><img class="img-fluid"
                                               src="{{ asset('assets/images/landing/1.png') }}"
                                               alt="">
                        <div class="group-link"><a class="hover-link"
                                                   href="javascript:void(0);"><img
                                    class="img-fluid"
                                    src="{{ asset('assets/images/landing/feathure/bootstrap.png') }}"
                                    alt=""></a><a class="hover-link"
                                                  href="javascript:void(0);"><img
                                    class="img-fluid"
                                    src="{{ asset('assets/images/landing/feathure/tailwind.png') }}"
                                    alt=""></a></div>
                    </div>
                    <div class="demo-detail">
                        <h3>{{__('Dashboard')}}</h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 wow fadeInUp">
                <div class="demo-grid">
                    <div class="img-wrap"><img class="img-fluid"
                                               src="{{ asset('assets/images/landing/2.png') }}"
                                               alt="">
                        <div class="group-link"><a class="hover-link"
                                                   href="javascript:void(0);"><img
                                    class="img-fluid"
                                    src="{{ asset('assets/images/landing/feathure/bootstrap.png') }}"
                                    alt=""></a><a class="hover-link"
                                                  href="javascript:void(0);"><img
                                    class="img-fluid"
                                    src="{{ asset('assets/images/landing/feathure/tailwind.png') }}"
                                    alt=""></a></div>
                    </div>
                    <div class="demo-detail">
                        <h3>{{__('Cars')}}</h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 wow fadeInUp">
                <div class="demo-grid">
                    <div class="img-wrap"><img class="img-fluid"
                                               src="{{ asset('assets/images/landing/4.png') }}"
                                               alt="">
                        <div class="group-link"><a class="hover-link"
                                                   href="javascript:void(0);"><img
                                    class="img-fluid"
                                    src="{{ asset('assets/images/landing/feathure/bootstrap.png') }}"
                                    alt=""></a><a class="hover-link"
                                                  href="javascript:void(0);"><img
                                    class="img-fluid"
                                    src="{{ asset('assets/images/landing/feathure/tailwind.png') }}"
                                    alt=""></a></div>
                    </div>
                    <div class="demo-detail">
                        <h3>{{__('Planning')}}</h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 wow fadeInUp">
                <div class="demo-grid">
                    <div class="img-wrap"><img class="img-fluid"
                                               src="{{ asset('assets/images/landing/5.png') }}"
                                               alt="">
                        <div class="group-link"><a class="hover-link"
                                                   href="javascript:void(0);"><img
                                    class="img-fluid"
                                    src="{{ asset('assets/images/landing/feathure/bootstrap.png') }}"
                                    alt=""></a><a class="hover-link"
                                                  href="javascript:void(0);"><img
                                    class="img-fluid"
                                    src="{{ asset('assets/images/landing/feathure/tailwind.png') }}"
                                    alt=""></a></div>
                    </div>
                    <div class="demo-detail">
                        <h3>{{__('Rental Agreement')}}</h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 wow fadeInUp">
                <div class="demo-grid">
                    <div class="img-wrap"><img class="img-fluid"
                                               src="{{ asset('assets/images/landing/7.png') }}"
                                               alt="">
                        <div class="group-link"><a class="hover-link"
                                                   href="javascript:void(0);"><img
                                    class="img-fluid"
                                    src="{{ asset('assets/images/landing/feathure/bootstrap.png') }}"
                                    alt=""></a><a class="hover-link"
                                                  href="javascript:void(0);"><img
                                    class="img-fluid"
                                    src="{{ asset('assets/images/landing/feathure/tailwind.png') }}"
                                    alt=""></a></div>
                    </div>
                    <div class="demo-detail">
                        <h3>{{__('User Role & Permission')}}</h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 wow fadeInUp">
                <div class="demo-grid">
                    <div class="img-wrap"><img class="img-fluid"
                                               src="{{ asset('assets/images/landing/6.png') }}"
                                               alt="">
                        <div class="group-link"><a class="hover-link"
                                                   href="javascript:void(0);"><img
                                    class="img-fluid"
                                    src="{{ asset('assets/images/landing/feathure/bootstrap.png') }}"
                                    alt=""></a><a class="hover-link"
                                                  href="javascript:void(0);"><img
                                    class="img-fluid"
                                    src="{{ asset('assets/images/landing/feathure/tailwind.png') }}"
                                    alt=""></a></div>
                    </div>
                    <div class="demo-detail">
                        <h3>{{__('Inspection Details')}}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- innderpages end-->

<!-- header otpion start-->
<section class="landheader-comp space-py-100 overflow-visible" id="faq">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
                <div class="land-title">
                    <h2 class="wow fadeInLeft">{{__('Frequently Asked Questions')}}</h2>
                </div>
            </div>
        </div>
        <div class="row pricing-grid">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>{{__('Installation Question')}}</h4>
                    </div>
                    <div class="card-body">
                        <div class="codex-accordion accordion accordion-flush" id="install-que">
                            <div class="accordion-item">                <a class="cdx-collapse collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#cdx-collapce1">{{__('What does LOREM mean?')}}</a>
                                <div class="collapse" id="cdx-collapce1" data-bs-parent="#install-que">
                                    <div class="accordion-body">
                                        <p class="text-light">{{__('‘Lorem ipsum dolor sit amet, consectetur adipisici elit…’ (complete text) is dummy text that is not meant to mean anything. It is used as a placeholder in magazine layouts, for example, in order to give an impression of the finished document. The text is intentionally unintelligible so that the viewer is not distracted by the content. The language is not real Latin and even the first word ‘Lorem’ does not exist. It is said that the lorem ipsum.')}}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">                <a class="cdx-collapse collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#cdx-collapce2">{{__('Where can I subscribe to your newsletter?')}}</a>
                                <div class="collapse" id="cdx-collapce2" data-bs-parent="#install-que">
                                    <div class="accordion-body">
                                        <p class="text-light">{{__('‘Lorem ipsum dolor sit amet, consectetur adipisici elit…’ (complete text) is dummy text that is not meant to mean anything. It is used as a placeholder in magazine layouts, for example, in order to give an impression of the finished document. The text is intentionally unintelligible so that the viewer is not distracted by the content. The language is not real Latin and even the first word ‘Lorem’ does not exist. It is said that the lorem ipsum.')}}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">                <a class="cdx-collapse collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#cdx-collapce3">{{__('Where can in edit my address?')}}</a>
                                <div class="collapse" id="cdx-collapce3" data-bs-parent="#install-que">
                                    <div class="accordion-body">
                                        <p class="text-light">{{__('‘Lorem ipsum dolor sit amet, consectetur adipisici elit…’ (complete text) is dummy text that is not meant to mean anything. It is used as a placeholder in magazine layouts, for example, in order to give an impression of the finished document. The text is intentionally unintelligible so that the viewer is not distracted by the content. The language is not real Latin and even the first word ‘Lorem’ does not exist. It is said that the lorem ipsum.')}}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">                <a class="cdx-collapse collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#cdx-collapce4">{{__('Can I order a free copy of a magazine to sample?')}}</a>
                                <div class="collapse" id="cdx-collapce4" data-bs-parent="#install-que">
                                    <div class="accordion-body">
                                        <p class="text-light">{{__('‘Lorem ipsum dolor sit amet, consectetur adipisici elit…’ (complete text) is dummy text that is not meant to mean anything. It is used as a placeholder in magazine layouts, for example, in order to give an impression of the finished document. The text is intentionally unintelligible so that the viewer is not distracted by the content. The language is not real Latin and even the first word ‘Lorem’ does not exist. It is said that the lorem ipsum.')}}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">                <a class="cdx-collapse collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#cdx-collapce5">{{__('Do you accept orders via Phone or E-mail?')}}</a>
                                <div class="collapse" id="cdx-collapce5" data-bs-parent="#install-que">
                                    <div class="accordion-body">
                                        <p class="text-light">{{__('‘Lorem ipsum dolor sit amet, consectetur adipisici elit…’ (complete text) is dummy text that is not meant to mean anything. It is used as a placeholder in magazine layouts, for example, in order to give an impression of the finished document. The text is intentionally unintelligible so that the viewer is not distracted by the content. The language is not real Latin and even the first word ‘Lorem’ does not exist. It is said that the lorem ipsum.')}}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- header otpion End-->
<!-- footer start-->
<footer class="lan-footer space-py-10">
    <div class="container">
        <div class="row">
            <div class="col text-center">
                <div class="support-contain">
                    <div class="codex-brand">
                        <p class="mt-20 mb-20">{{__('Copyright')}} {{date('Y')}} {{env('APP_NAME')}}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
<!-- footer end-->
<!-- tap to top start-->
<div class="scroll-top"><i class="fa fa-angle-double-up"></i></div>
<!-- tap to top end-->
<!-- main jquery-->
<script src="{{ asset('assets/js/jquery.js') }}"></script>
<!-- Feather iocns js-->
<script src="{{ asset('assets/js/icons/feather-icon/feather.js') }}"></script>
<!-- Wow js-->
<script src="{{ asset('assets/js/vendors/wow.min.js') }}"></script>

<script src="{{ asset('assets/js/vendors/slider/slick-sldier/slick.js') }}"></script>
<script src="{{ asset('assets/js/vendors/slider/slick-sldier/slick-custom.js') }}"></script>

<!-- Bootstrap js-->
<script src="{{ asset('assets/js/bootstrap.bundle.js') }}"></script>
<script>
    //*** Header Js ***//
    $(window).scroll(function () {
        if ($(window).scrollTop() > 100) {
            $('header').addClass('sticky');
        } else {
            $('header').removeClass('sticky');
        }
    });

    //*** Menu Js ***//
    $(document).on("click", '.menu-action', function () {
        $('.menu-list').toggleClass('open');
    });
    $(document).on("click", '.close-menu', function () {
        $('.menu-list').removeClass('open');
    });

    //*** BACK TO TOP START ***//
    $(window).scroll(function () {
        if ($(window).scrollTop() > 450) {
            $('.scroll-top').addClass('show');
        } else {
            $('.scroll-top').removeClass('show');
        }
    });
    $(document).ready(function () {
        $(document).on("click", '.scroll-top', function () {
            $('html, body').animate({scrollTop: 0}, '450');
        });
    });

    //*** WOW Js ***//
    new WOW().init();
</script>
</body>
</html>
