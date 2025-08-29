<!-- Meta & Head Assets -->
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="author" content="pixydrops">
<meta name="description" content="@yield('meta_description','Remons - Booking Rental HTML Template')">
<title>@yield('title','Remons - Booking Rental')</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link rel="shortcut icon" href="{{ asset('assets/images/client/favicon.png') }}">
<link rel="stylesheet" href="{{ asset('assets/css/client/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/client/all.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/client/animate.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/client/magnific-popup.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/client/meanmenu.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/client/datepickerboot.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/client/swiper-bundle.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/client/nice-select.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/client/main.css') }}">

@stack('styles')
