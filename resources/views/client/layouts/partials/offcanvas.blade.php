@php
    $settings = \App\Models\Setting::pluck('value', 'name')->toArray();
@endphp
<!-- Offcanvas Area Start -->
<div class="fix-area">
    <div class="offcanvas__info">
        <div class="offcanvas__wrapper">
            <div class="offcanvas__content">
                <div class="offcanvas__top mb-5 d-flex justify-content-between align-items-center">
                    <div class="offcanvas__logo">
                        <a href="{{ url('/') }}">
                            <img src="{{ asset(Storage::url('upload/logo/' . $settings['company_logo'])) }}" alt="logo-img">
                        </a>
                    </div>
                    <div class="offcanvas__close">
                        <button type="button">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <p class="text d-none d-xl-block">
                    Nullam dignissim, ante scelerisque the is euismod fermentum odio sem semper the is erat, a feugiat leo urna eget eros.
                </p>
                <div class="mobile-menu fix mb-3"></div>
                <div class="offcanvas__contact">
                    <h4>Contact Info</h4>
                    <ul class="list-unstyled">
                        <li class="d-flex align-items-center">{{ $settings['company_email'] }}</li>
                        <li class="d-flex align-items-center">{{ $settings['company_phone'] }}</li>
                        <li class="d-flex align-items-center">{{ $settings['company_address'] }}</li>
                    </ul>
                    <div class="header-button mt-4">
                        <a href="{{ Route::has('contact') ? route('contact') : url('/contact') }}" class="theme-btn text-center">
                            <span>Get A Quote <i class="fa-solid fa-arrow-right-long"></i></span>
                        </a>
                    </div>
                    <div class="social-icon d-flex align-items-center">
                        <a href="https://www.facebook.com/profile.php?id=100075895973021"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="offcanvas__overlay"></div>
