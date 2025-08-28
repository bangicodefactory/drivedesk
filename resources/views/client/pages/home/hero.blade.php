<!-- Hero Section Start -->
@php
    // $users = \Auth::user();
    $languages = \App\Models\Custom::languages();
    // $userLang = \Auth::user()->lang;
    $settings = \App\Models\Setting::pluck('value', 'name')->toArray();

    $displayLanguages = [
        'ar' => 'Arabic',
        'fr' => 'French',
        'en' => 'English',
    ];
@endphp
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
                <div class="hero-image bg-cover"
                    style="background-image: url('{{ Storage::url('upload/home/' . $settings['image_home_1']) }}');">
                    {{-- <div class="overlay-shape">
                        <img src="{{ asset('assets/images/client/hero/overlay.png') }}" alt="img">
                    </div> --}}
                </div>
            </div>
            <div class="swiper-slide">
                <div class="hero-image bg-cover"
                style="background-image: url('{{ Storage::url('upload/home/' . $settings['image_home_2']) }}');">
                    {{-- <div class="overlay-shape">
                        <img src="{{ asset('assets/images/client/hero/overlay.png') }}" alt="img">
                    </div> --}}
                </div>
            </div>
        </div>
    </div>
</section>
