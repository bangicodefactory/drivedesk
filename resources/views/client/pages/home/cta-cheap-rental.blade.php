<!-- Cta Cheap Rental Section Start -->
@php
    // settings(), not a bare pluck: this returns the acting tenant's rows
    // defaulted from settingsKeys(), so a key the deployment never set is
    // an empty string rather than a missing index. Unguarded reads of
    // company_name here 500'd /contact and /search on any deployment whose
    // branding_seed omits it -- drivedesk's does. The bare pluck was also
    // unscoped, merging every tenant's settings with the last one winning.
    $settings = settings();
    $logoPath = 'upload/logo/' . $settings['company_logo'];
@endphp
<section class="cta-cheap-rental-section">
    <div class="container">
        <div class="cta-cheap-rental">
            <div class="cta-cheap-rental-left wow fadeInUp" data-wow-delay=".3s">
                <div class="logo-thumb">
                    <a href="{{ url('/') }}">
                        <img class="img-fluid" style="max-height: 100px;"
                            src="{{ Storage::exists($logoPath) ? asset(Storage::url($logoPath)) : asset('assets/images/client/Logo-Direct.png') }}"
                            alt="theme-logo">
                    </a>
                </div>
                <h4 class="text-white">{{ __('cta_cheap_rental_save_big') }}</h4>
            </div>
            <div class="cta-cheap-rental-right ms-auto wow fadeInUp" data-wow-delay=".5s">
                <div class="social-icon d-flex align-items-center gap-3">
                    <a href="https://www.facebook.com/profile.php?id=100075895973021"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    {{-- <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a> --}}
                </div>
            </div>
        </div>
</section>
