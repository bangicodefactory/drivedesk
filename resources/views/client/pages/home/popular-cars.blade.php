<!-- Popular Car Section Start -->
<section class="popular-car-section fix section-padding">
    <div class="container">
        <div class="section-title text-center">
            <img src="{{ asset('assets/images/client/sub-icon.png') }}" alt="icon-img" class="wow fadeInUp">
            <span class="wow fadeInUp" data-wow-delay=".2s">select car types</span>
            <h2 class="wow fadeInUp" data-wow-delay=".4s">We’re Offering Popular <br> Cars Models</h2>
        </div>
        <div class="row g-4 mt-30">
            @php($cars = ['Sedan','Sports','Jeep'])
            @foreach($cars as $index=>$name)
            <div class="col-xl-4 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay=".{{ 3 + ($index%3)*2 }}s">
                <div class="popular-card-items">
                    <div class="content">
                        <h4><a href="#">{{ $name }}</a></h4>
                        <p>Available for Rent</p>
                    </div>
                    <div class="car-image">
                        <img src="{{ asset('assets/images/client/car/popular-car-'.($index+1).'.jpg') }}" alt="img">
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="popular-car-text wow fadeInUp" data-wow-delay=".4s">
            <h6>Car rental services specifically for our customers.</h6>
            <a href="#" class="theme-btn">Find a car</a>
        </div>
    </div>
</section>
