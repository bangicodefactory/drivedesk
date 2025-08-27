<!-- Meta & Head Assets -->
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="author" content="pixydrops">
<meta name="description" content="@yield('meta_description','Remons - Booking Rental HTML Template')">
<title>@yield('title','Remons - Booking Rental')</title>
<link rel="shortcut icon" href="{{ asset('assets/img/favicon.png') }}">
<link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/client/all.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/animate.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/magnific-popup.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/meanmenu.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/datepickerboot.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/swiper-bundle.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/nice-select.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
@stack('styles')
