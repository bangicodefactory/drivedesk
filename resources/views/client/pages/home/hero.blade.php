<!-- Hero Section Start -->
<section class="hero-section hero-1 fix">
    <div class="array-button">
        <button class="image-array-left">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
        <button class="image-array-right">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
    </div>
    <div class="swiper hero-slider">
        <div class="swiper-wrapper">
            <div class="swiper-slide">
                <div class="hero-image bg-cover" style="background-image: url('{{ asset('assets/img/hero/hero-1.jpg') }}');">
                    <div class="overlay-shape">
                        <img src="{{ asset('assets/img/hero/overlay.png') }}" alt="img">
                    </div>
                </div>
            </div>
            <div class="swiper-slide">
                <div class="hero-image bg-cover" style="background-image: url('{{ asset('assets/img/hero/hero-2.jpg') }}');">
                    <div class="overlay-shape">
                        <img src="{{ asset('assets/img/hero/overlay.png') }}" alt="img">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
