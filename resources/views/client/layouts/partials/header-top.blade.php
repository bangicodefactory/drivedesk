<!-- Header Top Section Start -->
@php
    // settings(), not a bare pluck: this returns the acting tenant's rows
    // defaulted from settingsKeys(), so a key the deployment never set is
    // an empty string rather than a missing index. Unguarded reads of
    // company_name here 500'd /contact and /search on any deployment whose
    // branding_seed omits it -- drivedesk's does. The bare pluck was also
    // unscoped, merging every tenant's settings with the last one winning.
    $settings = settings();
@endphp
<div class="header-top-section">
    <div class="container-fluid">
        <div class="header-top-wrapper">
            <ul class="contact-list">
                <li>
                    <i class="fas fa-envelope"></i>
                    <a href="mailto:{{$settings['company_email']}}" class="link">
                        {{ $settings['company_email'] ?: __('header_top_email') }}
                    </a>
                </li>
                <li>
                    <i class="fas fa-map-marker-alt"></i>
                    {{ $settings['company_address'] ?: __('header_top_address') }}
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
