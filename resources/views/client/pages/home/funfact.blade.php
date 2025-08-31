<section class="funfact-section section-padding bg-cover position-relative"
         style="background-image: url('{{ asset('assets/images/client/funfact-bg.jpg') }}'); background-size: cover; background-position: top center;">

    <!-- Red overlay with opacity -->
    <div style="position: absolute; top:0; left:0; width:100%; height:100%; background-color: rgba(255,0,0,0.5); z-index:1;"></div>

    <!-- Content above overlay -->
    <div class="container position-relative" style="z-index:2;">
        <div class="funfact-wrapper">
            <div class="row g-4 align-items-center">
                <div class="col-lg-6">
                    <div class="section-title mb-0">
                        <img src="{{ asset('assets/images/client/sub-icon-2.png') }}" alt="icon-img" class="wow fadeInUp">
                        <span class="wow fadeInUp" data-wow-delay=".2s">{{ __('funfact_fun_facts') }}</span>
                        <h2 class="text-white wow fadeInUp" data-wow-delay=".4s">{{ __('funfact_heading') }}</h2>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="funfact-counter-area">
                        @foreach([
                            ['count'=>50,'label'=>__('funfact_cars_available')],
                            ['count'=>800,'label'=>__('funfact_happy_clients')],
                            ['count'=>7,'label'=>__('funfact_years_of_service')]
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
