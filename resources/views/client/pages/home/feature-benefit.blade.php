<!-- Feature Benefit Section Start -->
<section class="feature-benefit section section-padding fix">
    <div class="container">
        <div class="section-title text-center">
            <img src="{{ asset('assets/images/client/sub-icon.png') }}" alt="icon-img" class="wow fadeInUp">
            <span class="wow fadeInUp" data-wow-delay=".2s">our benefits</span>
            <h2 class="wow fadeInUp" data-wow-delay=".4s">Why You Should Use <br> Remons Rental</h2>
        </div>
        <div class="row">
            @foreach([1,2,3] as $i)
                <div class="col-xl-4 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay="{{ '.' . (2*$i+1) }}s">
                    <div class="feature-benefit-items">
                        <div class="icon-box-shape">
                            <img src="{{ asset('assets/images/client/feature-benefit/box-icon-bg'.$i.'.png') }}" alt="shape-img">
                        </div>
                        <div class="bg-button-shape">
                            <img src="{{ asset('assets/images/client/feature-benefit/bg-button-iconbox.png') }}" alt="shape-img">
                        </div>
                        <div class="feature-content">
                            <h4><a href="#">Benefit Title {{ $i }}</a></h4>
                            <p>Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet.</p>
                            <div class="icon">
                                <img src="{{ asset('assets/images/client/feature-benefit/icon-'.$i.'.png') }}" alt="icon-img">
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
