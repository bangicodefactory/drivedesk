<!-- Funfact Section Start -->
<section class="funfact-section section-padding bg-cover" style="background-image: url('{{ asset('assets/img/funfact-bg.jpg') }}');">
    <div class="container">
        <div class="funfact-wrapper">
            <div class="row g-4 align-items-center">
                <div class="col-lg-6">
                    <div class="section-title mb-0">
                        <img src="{{ asset('assets/img/sub-icon-2.png') }}" alt="icon-img" class="wow fadeInUp">
                        <span class="wow fadeInUp" data-wow-delay=".2s">fun facts</span>
                        <h2 class="text-white wow fadeInUp" data-wow-delay=".4s">Driving Excellence <br> For Our Customers</h2>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="funfact-counter-area">
                        @foreach([
                            ['count'=>120,'label'=>'Cars Available'],
                            ['count'=>450,'label'=>'Happy Clients'],
                            ['count'=>25,'label'=>'Awards Won']
                        ] as $idx=>$fact)
                        <div class="funfact-items wow fadeInUp" data-wow-delay=".{{ 3 + $idx*2 }}s">
                            <h2 class="text-white"><span class="count">{{ $fact['count'] }}</span>+</h2>
                            <p class="text-white">{{ $fact['label'] }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
