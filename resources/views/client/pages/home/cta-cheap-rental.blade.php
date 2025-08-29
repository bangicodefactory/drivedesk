<!-- Cta Cheap Rental Section Start -->
@php
    $settings = \App\Models\Setting::pluck('value', 'name')->toArray();
@endphp
<section class="cta-cheap-rental-section">
    <div class="container">
        <div class="cta-cheap-rental">
            <div class="cta-cheap-rental-left wow fadeInUp" data-wow-delay=".3s">
                <div class="logo-thumb">
                    <a href="{{ url('/') }}">
                        <img class="img-fluid" style="max-height: 100px;"
                            src="{{ asset(Storage::url('upload/logo/' . $settings['company_logo'])) }}" alt="theme-logo">
                    </a>
                </div>
                <h4 class="text-white">Save big with our cheap car rental</h4>
            </div>
            <div class="social-icon d-flex align-items-center wow fadeInUp" data-wow-delay=".5s">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fa-brands fa-linkedin-in"></i></a>
                <a href="#"><i class="fa-brands fa-youtube"></i></a>
            </div>
        </div>
    </div>
</section>
