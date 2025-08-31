<section class="about-section fix section-padding pt-0">
    <div class="container">
        <div class="about-wrapper">
            <div class="row g-4">
                <!-- Left Image/Counter Column -->
                <div class="col-lg-6">
                    <div class="about-image-items position-relative">
                        <div class="color-shape">
                            <img src="{{ asset('assets/images/client/about/secondary-shape-color-full.png') }}"
                                 alt="shape-img"
                                 style="width: 100%; height: auto;">
                        </div>
                        <div class="car-shape wow fadeInUp" data-wow-delay=".7s">
                            <img src="{{ asset('assets/images/client/about/car-shape.png') }}"
                                 alt="shape-img"
                                 style="width: 80%; max-width: 400px; height: auto;">
                        </div>
                        <div class="counter-content wow fadeInLeft" data-wow-delay=".4s">
                            <h2 class="text-white"><span class="count">7</span></h2>
                            <p class="text-white">{{ __('about_years_experience') }}</p>
                        </div>
                        <div class="about-image-1 wow fadeInDown" data-wow-delay=".3s">
                            <img src="{{ asset('assets/images/client/about/01.jpg') }}"
                                 alt="about-image"
                                 style="width: 100%; max-width: 400px; height: auto; object-fit: cover;">
                        </div>
                        <div class="about-image-2 wow fadeInLeft" data-wow-delay=".5s">
                            <img src="{{ asset('assets/images/client/about/02.jpg') }}"
                                 alt="about-image"
                                 style="width: 100%; max-width: 400px; height: auto; object-fit: cover;">
                        </div>
                    </div>
                </div>

                <!-- Right Content Column -->
                <div class="col-lg-6">
                    <div class="about-content">
                        <div class="section-title">
                            <img src="{{ asset('assets/images/client/sub-icon.png') }}" alt="icon-img" class="wow fadeInUp">
                            <span class="wow fadeInUp" data-wow-delay=".2s">{{ __('about_get_to_know_us') }}</span>
                            <h2 class="wow fadeInUp" data-wow-delay=".4s">{{ __('about_best_rental_experience') }}</h2>
                        </div>

                        <h4 class="mt-3 mt-md-0 wow fadeInUp" data-wow-delay=".3s">
                            {{ __('about_committed_service') }}
                        </h4>

                        <p class="wow fadeInUp" data-wow-delay=".5s">
                            {{ __('about_description') }}
                        </p>

                        <div class="about-list-item wow fadeInUp" data-wow-delay=".7s">
                            <ul>
                                <li>{{ __('about_list_vehicles_range') }}</li>
                                <li>{{ __('about_list_pricing') }}</li>
                            </ul>
                            <ul>
                                <li>{{ __('about_list_customer_support') }}</li>
                                <li>{{ __('about_list_online_booking') }}</li>
                            </ul>
                        </div>

                        <a href="#" class="theme-btn wow fadeInUp" data-wow-delay=".8s">{{ __('about_discover_more') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
