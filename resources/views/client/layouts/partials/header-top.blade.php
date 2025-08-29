<!-- Header Top Section Start -->
@php
    $settings = \App\Models\Setting::pluck('value', 'name')->toArray();
@endphp
<div class="header-top-section">
    <div class="container-fluid">
        <div class="header-top-wrapper">
            <ul class="contact-list">
                <li>
                    <i class="fas fa-envelope"></i>
                    <a href="mailto:{{$settings['company_email']}}" class="link">{{$settings['company_email']}}</a>
                </li>
                <li>
                    <i class="fas fa-map-marker-alt"></i>
                    {{$settings['company_address']}}
                </li>
            </ul>
            <div class="header-top-right">
                <ul class="top-list">
                    <li><a href="{{ Route::has('contact') ? route('contact') : url('/contact') }}">Help</a></li>
                    <li>/</li>
                    <li><a href="{{ Route::has('contact') ? route('contact') : url('/contact') }}">Support</a></li>
                    <li>/</li>
                    <li><a href="{{ Route::has('contact') ? route('contact') : url('/contact') }}">Contact</a></li>
                </ul>
                <div class="social-icon d-flex align-items-center">
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fa-brands fa-facebook"></i></a>
                    <a href="#"><i class="fa-brands fa-pinterest-p"></i></a>
                    <a href="#"><i class="fa-brands fa-instagram"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>
