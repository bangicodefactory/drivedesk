<!-- News Section Start -->
<section class="news-section section-padding fix">
    <div class="container">
        <div class="section-title text-center">
            <img src="{{ asset('assets/images/client/sub-icon.png') }}" alt="icon-img" class="wow fadeInUp">
            <span class="wow fadeInUp" data-wow-delay=".2s">From the Blog</span>
            <h2 class="wow fadeInUp" data-wow-delay=".4s">Latest News & <br> Articles From the Blog</h2>
        </div>
        <div class="row">
            @foreach ([1, 2, 3] as $i)
                <div class="col-xl-4 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay=".{{ 2 * $i + 1 }}s">
                    <div class="news-card-items">
                        <div class="news-image">
                            <img src="{{ asset('assets/images/client/news/0' . $i . '.jpg') }}" alt="news-img">
                            <div class="post-date"></div>
                        </div>
                        <div class="news-content">
                            <div class="post-client"><img src="assets/images/client/instagram-icon.png" alt="img">
                            </div>
                            <div class="news-cont">
                                <h5><a href="#">Sample Blog Post {{ $i }}</a></h5>
                                <p>Brief description about blog post {{ $i }}.</p>
                            </div>
                            <ul></ul>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
