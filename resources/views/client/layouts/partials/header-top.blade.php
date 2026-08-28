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
                    <a href="mailto:{{$settings['company_email']}}" class="link">
                        {{ $settings['company_email'] ?? __('header_top_email') }}
                    </a>
                </li>
                <li>
                    <i class="fas fa-map-marker-alt"></i>
                    {{ $settings['company_address'] ?? __('header_top_address') }}
                </li>
            </ul>
            <div class="header-top-right">
                <ul class="top-list">
                    <li><a href="{{ Route::has('contact') ? route('contact') : url('/contact') }}">{{ __('header_top_help') }}</a></li>
                    <li>/</li>
                    <li><a href="{{ Route::has('contact') ? route('contact') : url('/contact') }}">{{ __('header_top_support') }}</a></li>
                    <li>/</li>
                    <li><a href="{{ Route::has('contact') ? route('contact') : url('/contact') }}">{{ __('header_top_contact') }}</a></li>
                </ul>
                <div class="social-icon d-flex align-items-center">
                    {{-- <a href="#"><i class="fab fa-twitter"></i> {{ __('header_top_twitter') }}</a> --}}
                    <a href="https://www.facebook.com/profile.php?id=100075895973021"><i class="fab fa-facebook-f"></i> {{ __('header_top_facebook') }}</a>
                    {{-- <a href="#"><i class="fab fa-pinterest-p"></i> {{ __('header_top_pinterest') }}</a> --}}
                    <a href="#"><i class="fab fa-instagram"></i> {{ __('header_top_instagram') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>
