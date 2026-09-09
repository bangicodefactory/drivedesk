@php
    // settings(), not a bare pluck: this returns the acting tenant's rows
    // defaulted from settingsKeys(), so a key the deployment never set is
    // an empty string rather than a missing index. Unguarded reads of
    // company_name here 500'd /contact and /search on any deployment whose
    // branding_seed omits it -- drivedesk's does. The bare pluck was also
    // unscoped, merging every tenant's settings with the last one winning.
    $settings = settings();

    $heroSlides = [
        [
            'image' => Storage::exists('upload/home/' . ($settings['image_home_1'] ?? ''))
                ? Storage::url('upload/home/' . $settings['image_home_1'])
                : asset('assets/images/client/hero/default-hero-2.jpg'),
            'subtitle' => __('subtitle_1'),
            'title' => __('title_1'),
        ],
        [
            'image' => Storage::exists('upload/home/' . ($settings['image_home_1'] ?? ''))
                ? Storage::url('upload/home/' . $settings['image_home_1'])
                : asset('assets/images/client/hero/default-hero-1.jpg'),
            'subtitle' => __('subtitle_1'),
            'title' => __('title_1'),
        ],
        [
            'image' => Storage::exists('upload/home/' . ($settings['image_home_2'] ?? ''))
                ? Storage::url('upload/home/' . $settings['image_home_2'])
                : asset('assets/images/client/hero/default-hero-3.jpg'),
            'subtitle' => __('subtitle_2'),
            'title' => __('title_2'),
        ],
    ];
@endphp

<section class="hero-section hero-1 fix">
    <!-- Navigation buttons -->
    <div class="array-button">
        <button class="image-array-left">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
        <button class="image-array-right">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
    </div>

    <!-- Hero slider -->
    <div class="swiper hero-slider">
        <div class="swiper-wrapper">
            @foreach ($heroSlides as $slide)
                <div class="swiper-slide">
                    <div class="hero-image bg-cover" style="background-image: url('{{ $slide['image'] }}');">
                        <div class="overlay-shape">
                            <img src="{{ asset('assets/images/client/hero/overlay.png') }}" alt="img">
                        </div>
                    </div>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-xl-12">
                                <div class="hero-content text-center">
                                    <h4 class="text-white" data-animation="fadeInUp" data-delay="1.3s">
                                        {!! $slide['subtitle'] !!}
                                    </h4>
                                    <h1 class="text-white" data-animation="fadeInUp" data-delay="1.3s">
                                        {!! $slide['title'] !!}
                                    </h1>
                                    <div class="hero-button" data-animation="fadeInUp" data-delay="1.3s">
                                        <label class="field-label style-2"></label>
                                        <a href="#search" class="theme-btn">{{ __('find_car_button') }}</a>
                                    </div>
                                   
                                </div>
                            </div>
                          
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
   
</section>
