@php
    $vehicles = \App\Models\Vehicle::all();
@endphp

<section class="car-rentals-section section-padding fix">
    <div class="container">
        <div class="section-title text-center">
            <img src="{{ asset('assets/images/client/sub-icon.png') }}" alt="icon-img" class="wow fadeInUp">
            <span class="wow fadeInUp" data-wow-delay=".2s">Checkout our cars</span>
            <h2 class="wow fadeInUp" data-wow-delay=".4s">Cars We’re Offering <br> for Rentals</h2>
        </div>
    </div>

    <div class="car-rentals-wrapper">
        <div class="array-button">
            <button class="array-prev"><i class="fas fa-chevron-left"></i></button>
            <button class="array-next"><i class="fas fa-chevron-right"></i></button>
        </div>

        <div class="swiper car-rentals-slider">
            <div class="swiper-wrapper">
                @foreach($vehicles as $vehicle)
                <div class="swiper-slide">
                    <div class="car-rentals-items">
                        <div class="car-image">
                            <img src="{{ asset('storage/upload/picture/'.$vehicle->picture) }}" alt="{{ $vehicle->name }}">
                        </div>
                        <div class="car-content">
                            <h4><a href="#">{{ $vehicle->name }}  {{ $vehicle->model}}</a></h4>
                            <h6>
                                ${{ number_format($vehicle->daily_rate, 2) }}
                                <span>/ Day</span>
                            </h6>

                            {{-- @if($vehicle->status === 'available')
                                <a href="{{ route('booking.create', $vehicle->id) }}"
                                   class="theme-btn bg-color w-100 text-center">
                                    Book now <i class="fa-solid fa-arrow-right ps-1"></i>
                                </a>
                            @else
                                <span class="theme-btn bg-secondary w-100 text-center disabled">
                                    Not Available
                                </span>
                            @endif --}}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
