<!-- Testimonial Section Start -->
<section class="testimonial-section fix section-padding">
    <div class="testimonial-bg-shape">
        <img src="{{ asset('assets/images/client/testimonial/testimonial-bg.jpg') }}" alt="shape-img">
    </div>
    <div class="container">
        <div class="section-title-area">
            <div class="section-title">
                <img src="{{ asset('assets/images/client/sub-icon.png') }}" alt="icon-img" class="wow fadeInUp">
                <span class="wow fadeInUp" data-wow-delay=".2s">our testimonials</span>
                <h2 class="wow fadeInUp" data-wow-delay=".4s">What They're Talking <br> About Direct Onderweg</h2>
            </div>
            <p class="wow fadeInUp" data-wow-delay=".5s">
                Hear from our valued clients who have experienced our commitment to quality and service.
                Their stories reflect the trust, satisfaction, and long-lasting relationships we build every day at
                Direct Onderweg.
            </p>
        </div>

        <div class="swiper testimonial-slider">
            <div class="swiper-wrapper">
                @php
                    $testimonials = [
                        [
                            'name' => 'Jessica Brown',
                            'role' => 'Customer',
                            'image' => 'client-1.png',
                            'rating' => 4,
                            'text' =>
                                'I was very impressed by the remons service lorem ipsum is simply free text used by copy typing refreshing. Neque porro est qui dolorem ipsum quia.',
                        ],
                        [
                            'name' => 'Kevin Martin',
                            'role' => 'Customer',
                            'image' => 'client-2.png',
                            'rating' => 5,
                            'text' =>
                                'I was very impressed by the remons service lorem ipsum is simply free text used by copy typing refreshing. Neque porro est qui dolorem ipsum quia.',
                        ],
                        [
                            'name' => 'Jessica Brown',
                            'role' => 'Customer',
                            'image' => 'client-3.png',
                            'rating' => 5,
                            'text' =>
                                'I was very impressed by the remons service lorem ipsum is simply free text used by copy typing refreshing. Neque porro est qui dolorem ipsum quia.',
                        ],
                        [
                            'name' => 'Michael Johnson',
                            'role' => 'Customer',
                            'image' => 'client-4.png',
                            'rating' => 4,
                            'text' =>
                                'I was very impressed by the remons service lorem ipsum is simply free text used by copy typing refreshing. Neque porro est qui dolorem ipsum quia.',
                        ],
                    ];
                @endphp

                @foreach ($testimonials as $testimonial)
                    <div class="swiper-slide">
                        <div class="testimonial-card-items">
                            <div class="quote-icon">
                                <i class="fas fa-quote-left"></i>
                            </div>

                            <div class="testimonial-content">
                                <p>{{ $testimonial['text'] }}</p>

                                <div class="star-rating">
                                    @for ($i = 1; $i <= 5; $i++)
                                        @if ($i <= $testimonial['rating'])
                                            <i class="fas fa-star"></i>
                                        @else
                                            <i class="far fa-star"></i>
                                        @endif
                                    @endfor
                                </div>
                            </div>

                            <div class="client-info-items d-flex align-items-center gap-3">
                                <div class="client-thumb">
                                    <img src="{{ asset('assets/images/client/testimonial/' . $testimonial['image']) }}"
                                        alt="{{ $testimonial['name'] }}">
                                </div>
                                <div class="client-content">
                                    <h5>{{ $testimonial['name'] }}</h5>
                                    <p>{{ $testimonial['role'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="swiper-pagination"></div>
        </div>

        <!-- Navigation arrows -->
        <div class="testimonial-nav">
            <button class="testimonial-prev"><i class="fas fa-chevron-left"></i></button>
            <button class="testimonial-next"><i class="fas fa-chevron-right"></i></button>
        </div>
    </div>
</section>
