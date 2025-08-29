<!-- Pick Up Location Section Start -->
@php
    $vehicleTypes = \App\Models\VehicleType::all();
@endphp

<div class="pickup-loaction-area bg-cover"
    style="background-image: url('{{ asset('assets/images/client/brand-bg.png') }}');">
    <div class="container">
        <div class="pickup-wrapper wow fadeInUp" data-wow-delay=".4s">
            <form action="#" method="get" class="d-flex flex-wrap">
                <div class="pickup-items">
                    <label class="field-label">Pick-up Location</label>
                    <div class="category-oneadjust">
                        <select name="location" class="category">
                            <option value="">Select Location</option>
                            @foreach (\App\Models\Place::all() as $place)
                                <option value="{{ $place->name }}">{{ $place->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="pickup-items">
                    <label class="field-label">Pickup Date</label>
                    <div id="datepicker" class="input-group date" data-date-format="dd-mm-yyyy">
                        <input class="form-control" name="pickup_date" type="text" placeholder="Check in" readonly>
                        <span class="input-group-addon"><i class="fa-solid fa-calendar-days"></i></span>
                    </div>
                </div>
                <div class="pickup-items">
                    <label class="field-label">Dropoff Date</label>
                    <div id="datepicker2" class="input-group date" data-date-format="dd-mm-yyyy">
                        <input class="form-control" name="dropoff_date" type="text" placeholder="Check out" readonly>
                        <span class="input-group-addon"><i class="fa-solid fa-calendar-days"></i></span>
                    </div>
                </div>
                <div class="pickup-items">
                    <label class="field-label">Car Type</label>
                    <div class="category-oneadjust">
                        <select name="type" class="category">
                            <option value="">Select Car Type</option>
                            @foreach ($vehicleTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->type }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="pickup-items">
                    <label class="field-label style-2">&nbsp;</label>
                    <button class="pickup-btn" type="submit">Find a Car</button>
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
