<section class="popular-car-section fix section-padding">
    <div class="container">
        <div class="section-title text-center">
            <img src="{{ asset('assets/images/client/sub-icon.png') }}" alt="icon-img" class="wow fadeInUp">
            <span class="wow fadeInUp" data-wow-delay=".2s">{{ __('popular_car_select_types') }}</span>
            <h2 class="wow fadeInUp" data-wow-delay=".4s">{{ __('popular_car_heading') }}</h2>
        </div>
        <div class="row g-4 mt-30">
            @php($cars = [__('popular_car_suv'), __('popular_car_sports'), __('popular_car_hatchback')])
            @foreach($cars as $index=>$name)
            <div class="col-xl-4 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay=".{{ 3 + ($index%3)*2 }}s">
                <div class="popular-card-items">
                    <div class="content">
                        <h4><a href="#">{{ $name }}</a></h4>
                        <p>{{ __('popular_car_available_rent') }}</p>
                    </div>
                    <div class="car-image">
                        <img src="{{ asset('assets/images/client/car/popular-car-'.($index+1).'.jpg') }}" alt="img">
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="popular-car-text wow fadeInUp" data-wow-delay=".4s">
            <h6>{{ __('popular_car_description') }}</h6>
            <a href="#" class="theme-btn">{{ __('popular_car_find') }}</a>
        </div>
    </div>
</section>
