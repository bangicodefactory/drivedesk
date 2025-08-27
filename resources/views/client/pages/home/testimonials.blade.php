<!-- Testimonial Section Start -->
<section class="testimonial-section fix section-padding">
    <div class="testimonial-bg-shape">
        <img src="{{ asset('assets/img/testimonial/testimonial-bg.jpg') }}" alt="shape-img">
    </div>
    <div class="container">
        <div class="section-title-area">
            <div class="section-title">
                <img src="{{ asset('assets/img/sub-icon.png') }}" alt="icon-img" class="wow fadeInUp">
                <span class="wow fadeInUp" data-wow-delay=".2s">our testimonials</span>
                <h2 class="wow fadeInUp" data-wow-delay=".4s">What They’re Talking <br> About Remons</h2>
            </div>
            <p class="wow fadeInUp" data-wow-delay=".5s">Lorem ipsum dolor sit amet consectetur adipiscing elit.</p>
        </div>
        <div class="swiper testimonial-slider">
            <div class="swiper-wrapper">
                @foreach(range(1,3) as $i)
                <div class="swiper-slide">
                    <div class="testimonial-card-items">
                        <div class="testimoni-bg-shape"></div>
                        <div class="client-info-items d-flex align-items-center gap-3">
                            <div class="client-thumb"><img src="{{ asset('assets/img/testimonial/client-'.$i.'.jpg') }}" alt="client" onerror="this.style.display='none'"></div>
                            <div class="content">
                                <h5>Client {{ $i }}</h5>
                                <p>"Great rental experience!"</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
