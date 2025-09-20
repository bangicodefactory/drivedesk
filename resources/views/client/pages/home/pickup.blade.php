@php
$vehicleTypes = \App\Models\VehicleType::all();
@endphp
<div id="search" class="pickup-loaction-area bg-cover"
     style="background-image: url('{{ asset('assets/images/client/brand-bg.png') }}');">
    <div class="container">
        <div class="pickup-wrapper wow fadeInUp" data-wow-delay=".4s">
            <form action="#" method="get">
                <div class="pickup-items">
                    <label class="field-label">{{ __('pickup_location_label') }}</label>
                    <div class="category-oneadjust">
                        <select name="location" class="category">
                            <option value="">{{ __('pickup_location_select') }}</option>
                            @foreach (\App\Models\Place::all() as $place)
                                <option value="{{ $place->name }}">{{ $place->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="pickup-items">
                    <label class="field-label">{{ __('pickup_date_label') }}</label>
                    <div id="datepicker" class="input-group date" data-date-format="dd-mm-yyyy">
                        <input class="form-control" name="pickup_date" type="text" placeholder="{{ __('check_in_placeholder') }}" readonly>
                        <span class="input-group-addon"><i class="fa-solid fa-calendar-days"></i></span>
                    </div>
                </div>

                <div class="pickup-items">
                    <label class="field-label">{{ __('dropoff_date_label') }}</label>
                    <div id="datepicker2" class="input-group date" data-date-format="dd-mm-yyyy">
                        <input class="form-control" name="dropoff_date" type="text" placeholder="{{ __('check_out_placeholder') }}" readonly>
                        <span class="input-group-addon"><i class="fa-solid fa-calendar-days"></i></span>
                    </div>
                </div>

                <div class="pickup-items">
                    <label class="field-label">{{ __('car_type_label') }}</label>
                    <div class="category-oneadjust">
                        <select name="type" class="category">
                            <option value="">{{ __('select_car_placeholder') }}</option>
                            @foreach ($vehicleTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->type }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="pickup-items">
                    <label class="field-label style-2"></label>
                    <button class="pickup-btn" type="submit">{{ __('find_car_button') }}</button>
                </div>
            </form>
        </div>

        <div class="brand-wrapper pt-80 pb-80">
            <div class="array-button">
                <button class="array-prev-2"><i class="fas fa-chevron-left"></i></button>
                <button class="array-next-2"><i class="fas fa-chevron-right"></i></button>
            </div>
            <div class="swiper brand-slider">
                <div class="swiper-wrapper">
                    @for ($i = 1; $i <= 6; $i++)
                        <div class="swiper-slide">
                            <div class="brand-image">
                                <img src="{{ asset('assets/images/client/brand/0' . $i . '.png') }}" alt="brand">
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>
</div>
