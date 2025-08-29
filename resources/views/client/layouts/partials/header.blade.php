<!-- Header Section Start -->
@php
    $settings = \App\Models\Setting::pluck('value', 'name')->toArray();
@endphp
<header id="header-sticky" class="header-1">
    <div class="container-fluid">
        <div class="mega-menu-wrapper">
            <div class="header-main">
                <div class="header-left">
                    <div class="logo">
                        <a class="codexbrand-logo" href="{{ route('home') }}">
                            <img class="img-fluid" style="max-height: 100px;"
                                src="{{ asset(Storage::url('upload/logo/' . $settings['company_logo'])) }}"
                                alt="theme-logo">
                        </a>
                    </div>

                    <div class="mean__menu-wrapper">
                        <div class="main-menu">
                            <nav id="mobile-menu">
                                <ul>
                                    <li><a href="{{ url('/') }}">Home</a></li>
                                    <li><a href="#">Cars</a></li>
                                    <li><a href="#">About</a></li>
                                    <li><a href="#">Blog</a></li>
                                    <li><a href="{{ route('contact') }}">Contact</a></li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
                <div class="header-right d-flex justify-content-end align-items-center">
                    <div class="icon-items">
                        <div class="icon"><i class="fas fa-phone-alt"></i></div>
                        <div class="content">
                            <p>Call Anytime</p>
                            <h6><a href="tel:{{$settings['company_phone']}}">{{$settings['company_phone']}}</a></h6>
                        </div>
                    </div>
                    <a href="#0" class="search-trigger search-icon"><i
                            class="fa fa-magnifying-glass"></i></a>
                    <div class="header-button">
                        <a href="#" class="header-btn">Find a Car</a>
                    </div>
                    <div class="header__hamburger d-xl-none my-auto">
                        <div class="sidebar__toggle">
                            <i class="fa fa-bars"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
@include('client.layouts.partials.search')
