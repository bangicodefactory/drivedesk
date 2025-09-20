{{-- resources/views/partials/car-rentals.blade.php --}}
@php
    $vehicles = \App\Models\Vehicle::all();
@endphp

<section class="car-rentals-section section-padding fix">
    <div class="container">
        <div class="section-title text-center">
            <img src="{{ asset('assets/images/client/sub-icon.png') }}" alt="icon-img" class="wow fadeInUp">
            <span class="wow fadeInUp" data-wow-delay=".2s">{{ __('car_rentals_subtitle') }}</span>
            <h2 class="wow fadeInUp" data-wow-delay=".4s">{{ __('car_rentals_title') }}</h2>
        </div>
    </div>

    <div class="car-rentals-wrapper">
        <div class="swiper car-rentals-slider">
            <div class="swiper-wrapper">
                @foreach ($vehicles as $vehicle)
                    <div class="swiper-slide">
                        <div class="car-rental-card">
                            <div class="car-image-container">
                                <img src="{{ $vehicle->picture && file_exists(storage_path('app/public/upload/picture/' . $vehicle->picture))
                                    ? asset('storage/upload/picture/' . $vehicle->picture)
                                    : asset('assets/images/client/default-car.jpg') }}"
                                    alt="{{ $vehicle->name }}" class="car-image">

                                <div class="model-badge">
                                    <span class="badge">{{ __('car_model') }} {{ $vehicle->model }}</span>
                                </div>
                            </div>
                            <div class="car-content">
                                <div class="rating-section">
                                    <div class="stars">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <span class="review-count">2 {{ __('car_reviews') }}</span>
                                </div>
                                <h4 class="car-title">
                                    <a href="#">{{ $vehicle->name }} {{ $vehicle->model }}</a>
                                </h4>
                                <div class="price-section">
                                    <span class="price">{{ number_format($vehicle->daily_rate, 2) }} Dh</span>
                                    <span class="period">/ {{ __('car_per_day') }}</span>
                                </div>
                                <div class="car-specifications">
                                    <div class="spec-row">
                                        <div class="spec-item">
                                            <i class="fas fa-users spec-icon"></i>
                                            <span>{{ $vehicle->number_of_seats ?? '6' }} {{ __('car_seats') }}</span>
                                        </div>
                                        <div class="spec-item">
                                            <i class="fas fa-cog spec-icon"></i>
                                            <span>{{ $vehicle->gearbox ?? __('car_automatic') }}</span>
                                        </div>
                                    </div>
                                    <div class="spec-row">
                                        <div class="spec-item">
                                            <i class="fas fa-door-open spec-icon"></i>
                                            <span>{{ $vehicle->doors ?? '4' }} {{ __('car_doors') }}</span>
                                        </div>
                                        <div class="spec-item">
                                            <i class="fas fa-gas-pump spec-icon"></i>
                                            <span>{{ $vehicle->fuel_type ?? __('car_petrol') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="book-button">
                                    <a href="{{ route('client.details', $vehicle->id) }}"
                                        class="theme-btn bg-color w-100 text-center">
                                        {{ __('car_book_now') }} <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Navigation arrows -->
            <div class="testimonial-nav">
                <button class="testimonial-prev"><i class="fas fa-chevron-left"></i></button>
                <button class="testimonial-next"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </div>
</section>

<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        new Swiper('.car-rentals-slider', {
            loop: true,
            spaceBetween: 30,
            slidesPerView: 1,
            breakpoints: {
                640: {
                    slidesPerView: 2
                },
                1024: {
                    slidesPerView: 3
                },
            },
            navigation: {
                nextEl: '.testimonial-next',
                prevEl: '.testimonial-prev',
            },
        });
    });
</script>

<style>
    .car-rentals-section {
        position: relative;
    }

    .car-rentals-wrapper {
        position: relative;
        overflow: hidden;
        margin-bottom: 140px;
    }

    .testimonial-nav {
        position: absolute;
        top: 50%;
        width: 100%;
        z-index: 10;
        display: flex;
        justify-content: space-between;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .testimonial-nav button {
        pointer-events: auto;
        width: 50px;
        height: 50px;
        background: #fff;
        border: 2px solid #e5e7eb;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .testimonial-nav button:hover {
        background: #2563eb;
        color: white;
        border-color: #2563eb;
        transform: scale(1.1);
    }

    .testimonial-nav button i {
        font-size: 16px;
    }

    @media (max-width: 768px) {
        .testimonial-nav button {
            width: 40px;
            height: 40px;
        }
    }

    .swiper-slide {
        padding: 20px;
    }

    .car-rental-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, .08);
        transition: all .3s ease;
        overflow: hidden;
        border: 1px solid #f0f0f0;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .car-rental-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, .12);
    }

    .car-image-container {
        position: relative;
        width: 100%;
        height: 220px;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .car-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .3s ease;
    }

    .car-rental-card:hover .car-image {
        transform: scale(1.05);
    }

    .model-badge {
        position: absolute;
        top: 15px;
        right: 15px;
    }

    .badge {
        background: #2563eb;
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .car-content {
        padding: 20px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .rating-section {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    .stars {
        display: flex;
        gap: 2px;
    }

    .stars i {
        color: #fbbf24;
        font-size: 14px;
    }

    .review-count {
        color: #6b7280;
        font-size: 14px;
    }

    .car-title {
        font-size: 18px;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 12px;
        line-height: 1.3;
    }

    .car-title a {
        color: inherit;
        text-decoration: none;
    }

    .car-title a:hover {
        color: #2563eb;
    }

    .price-section {
        margin-bottom: 20px;
    }

    .price {
        font-size: 24px;
        font-weight: 700;
        color: #2563eb;
    }

    .period {
        color: #6b7280;
        font-size: 16px;
        font-weight: 400;
    }

    .car-specifications {
        margin-bottom: 20px;
        flex-grow: 1;
    }

    .spec-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    .spec-row:last-child {
        margin-bottom: 0;
    }

    .spec-item {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #6b7280;
        font-size: 14px;
        flex: 1;
    }

    .spec-icon {
        color: #ef4444;
        font-size: 16px;
        width: 16px;
    }

    @media (max-width: 768px) {
        .spec-row {
            flex-direction: column;
            gap: 8px;
        }

        .spec-item {
            justify-content: flex-start;
        }
    }
</style>
