<section class="news-section section-padding fix">
    <div class="container">
        <div class="section-title text-center">
            <img src="{{ asset('assets/images/client/instagram-icon.png') }}" alt="">
            <span class="wow fadeInUp" data-wow-delay=".2s">{{ __('instagram_subtitle') }}</span>
            <h2 class="wow fadeInUp" data-wow-delay=".4s">{!! __('instagram_title') !!}</h2>
        </div>
        <div class="row">
            @foreach ([1, 2, 3] as $i)
                <div class="col-xl-4 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay=".{{ 2 * $i + 1 }}s">
                    <div class="news-card-items">
                        <div class="news-image relative">
                            <img src="{{ asset('assets/images/client/insta/0' . $i . '.jpg') }}" alt="insta-post">
                            <!-- Instagram icon overlay -->
                            <a href="#" class="absolute bottom-3 right-3 bg-white rounded-full p-2 shadow">
                                <img src="{{ asset('assets/images/client/instagram-icon.png') }}" alt="">
                            </a>
                        </div>
                        <div class="news-content">
                            <div class="flex items-center gap-2 mb-2">
                                <i class="far fa-heart text-gray-500"></i>
                                <span>{{ rand(20, 150) }} {{ __('instagram_likes') }}</span>
                                <i class="far fa-comment text-gray-500 ml-4"></i>
                                <span>{{ rand(5, 30) }} {{ __('instagram_comments') }}</span>
                            </div>
                            <p>{{ __('instagram_caption', ['number' => $i]) }}</p>
                            <a href="#" class="theme-btn bg-color w-100 text-center">{{ __('instagram_view_button') }}</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
