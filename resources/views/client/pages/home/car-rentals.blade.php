<!-- Car Rentals Section Start -->
<section class="car-rentals-section section-padding fix">
    <div class="container">
        <div class="section-title text-center">
            <img src="{{ asset('assets/images/client/sub-icon.png') }}" alt="icon-img" class="wow fadeInUp">
            <span class="wow fadeInUp" data-wow-delay=".2s">Checkout our new cars</span>
            <h2 class="wow fadeInUp" data-wow-delay=".4s">Cars We’re Offering <br> for Rentals</h2>
        </div>
    </div>
    <div class="car-rentals-wrapper">
        <div class="array-button">
            <button class="array-prev"><i class="fas fa-chevron-left"></i></button>
            <button class="array-next"><i class="fas fa-chevron-right"></i></button>
        </div>
        <div class="swiper car-rentals-slider">
            <div class="swiper-wrapper">
                @foreach([1,2,3,4] as $i)
                <div class="swiper-slide">
                    <div class="car-rentals-items">
                        <div class="car-image">
                            <img src="{{ asset('assets/images/client/car/0'.$i.'.jpg') }}" alt="car">
                        </div>
                        <div class="car-content">
                            <h4><a href="#">Hyundai Accent Limited</a></h4>
                            <h6>$70.00 <span>/ Day</span></h6>
                            <a href="#" class="theme-btn bg-color w-100 text-center">book now <i class="fa-solid fa-arrow-right ps-1"></i></a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
