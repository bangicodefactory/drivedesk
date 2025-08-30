<!-- Feature Benefit Section Start -->
<section class="feature-benefit section section-padding fix">
    <div class="container">
        <div class="section-title text-center">
            <img src="{{ asset('assets/images/client/sub-icon.png') }}" alt="icon-img" class="wow fadeInUp">
            <span class="wow fadeInUp" data-wow-delay=".2s">our benefits</span>
            <h2 class="wow fadeInUp" data-wow-delay=".4s">Why You Should Use <br> Remons Rental</h2>
        </div>
        <div class="row">
            @php
                $features = [
                    [
                        'title' => 'Easier & Faster Bookings',
                        'description' => 'Reserve your car quickly and easily with our streamlined booking system, saving you time and effort.',
                        'icon' => 'icon-1.png',
                        'bg' => 'box-icon-bg1.png',
                        'delay' => '.2s'
                    ],
                    [
                        'title' => 'Too Many Pickup Locations',
                        'description' => 'Choose from a wide network of pickup locations that fit your schedule and convenience.',
                        'icon' => 'icon-2.png',
                        'bg' => 'box-icon-bg2.png',
                        'delay' => '.4s'
                    ],
                    [
                        'title' => 'Customers 100% Satisfied',
                        'description' => 'We prioritize customer satisfaction with excellent service, clean vehicles, and transparent pricing.',
                        'icon' => 'icon-3.png',
                        'bg' => 'box-icon-bg3.png',
                        'delay' => '.6s'
                    ]
                ];
            @endphp

            @foreach($features as $feature)
                <div class="col-xl-4 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay="{{ $feature['delay'] }}">
                    <div class="feature-benefit-items">
                        <div class="icon-box-shape">
                            <img src="{{ asset('assets/images/client/feature-benefit/'.$feature['bg']) }}" alt="shape-img">
                        </div>
                        <div class="bg-button-shape">
                            <img src="{{ asset('assets/images/client/feature-benefit/bg-button-iconbox.png') }}" alt="shape-img">
                        </div>
                        <div class="feature-content">
                            <h4><a href="#">{{ $feature['title'] }}</a></h4>
                            <p>{{ $feature['description'] }}</p>
                            <div class="icon">
                                <img src="{{ asset('assets/images/client/feature-benefit/'.$feature['icon']) }}" alt="icon-img">
                            </div>
                        </div>
                        <div class="feature-button">
                            <a href="#" class="link-btn">View More <i class="fa-solid fa-arrow-right ps-1"></i></a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
