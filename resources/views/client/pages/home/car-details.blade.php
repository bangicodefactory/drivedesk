<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $car->name ?? 'Car Details' }} - {{ config('app.name', 'Laravel') }}</title>
    
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        .car-image {
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .icon-items {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .icon-items .icon {
            width: 40px;
            margin-right: 15px;
        }
        .price-table-items {
            display: flex;
            justify-content: space-between;
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
        }
        .section-bg {
            background-color: #f9f9f9;
        }
        .theme-btn {
            background-color: #4e5ee4;
            color: white;
            padding: 12px 25px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            border: none;
        }
        .theme-btn:hover {
            background-color: #3a48c5;
            color: white;
        }
        .car-rentals-items {
            border: 1px solid #eee;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s;
            margin-bottom: 20px;
        }
        .car-rentals-items:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .breadcrumb-wrapper {
            padding: 60px 0;
            background-size: cover;
            background-position: center;
            color: white;
            position: relative;
        }
        .breadcrumb-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.6);
        }
        .page-heading {
            position: relative;
            z-index: 1;
        }
        .form-clt {
            margin-bottom: 20px;
        }
        .form-clt label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .form-clt input, .form-clt select, .form-clt textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .input-save-items-area {
            display: flex;
            justify-content: space-between;
        }
        .car-single-comment {
            border-bottom: 1px solid #eee;
        }
        .star {
            color: #ffc107;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            color: #3c763d;
            background-color: #dff0d8;
            border-color: #d6e9c6;
        }
        .alert-danger {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }
    </style>
</head>
<body>
    <!-- Display Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <!-- Breadcrumb Section -->
    <div class="breadcrumb-wrapper bg-cover" style="background-image: url('{{ asset('assets/img/bg-header-banner.jpg') }}');">
        <div class="container">
            <div class="page-heading">
                <ul class="breadcrumb-items">
                    <li>
                        <a href="{{ route('home') }}">Home</a>
                    </li>
                    <li>
                        <i class="fas fa-chevron-right"></i>
                    </li>
                    <li>
                        <a >Cars</a> 
                    </li>
                    <li>
                        <i class="fas fa-chevron-right"></i>
                    </li>
                    <li>
                        {{ $car->name ?? 'Car Details' }}
                    </li>
                </ul>
                <h1>{{ $car->name ?? 'Car Details' }}</h1>
            </div>
        </div>
    </div>

    <!-- Car Details Section -->
    <section class="car-details fix section-padding">
        <div class="container">
            <div class="car-details-wrapper">
                <div class="row g-5">
                    <div class="col-lg-8">
                        <div class="car-details-items">
                            <div class="car-image">
                            <img src="{{ asset('storage/' . $car->picture) }}" alt="{{ $car->name ?? 'Car Image' }}">
                            </div>
                            <div class="car-content">
                                <div class="star">
                                    @for($i = 0; $i < 5; $i++)
                                        <i class="fa-solid fa-star"></i>
                                    @endfor
                                    <span>2 Reviews</span>
                                </div>
                                <h3>{{ $car->name ?? 'Hyundai Accent Limited' }}</h3>
                                <h6>MAD{{ number_format($car->daily_rate ?? 70, 2) }} <span>/ Day</span></h6>
                                <p class="mt-4 mb-4">
                                    {{ $car->notes ?? 'N/A' }}
                                </p>
                                <div class="icon-details-area">
                                    <h4>Key Features</h4>
                                    <div class="icon-details-main-items">
                                        <div class="icon-items">
                                            <div class="icon">
                                                <img src="{{ asset('assets/img/car/icon/07.png') }}" alt="body type">
                                            </div>
                                            <div class="content">
                                                <h6>Type:</h6>
                                                <p>{{ $car->types->type ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                        <div class="icon-items">
                                            <div class="icon">
                                                <img src="{{ asset('assets/img/car/icon/07.png') }}" alt="mileage">
                                            </div>
                                            <div class="content">
                                                <h6>Mileage:</h6>
                                                <p>{{ number_format($car->kilometers ?? 'N/A') }} (KM)</p>
                                            </div>
                                        </div>
                                        <div class="icon-items">
                                            <div class="icon">
                                                <img src="{{ asset('assets/img/car/icon/07.png') }}" alt="year">
                                            </div>
                                            <div class="content">
                                                <h6>Year:</h6>
                                                <p>{{ $car->year_of_first_immatriculation ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                        <div class="icon-items">
                                            <div class="icon">
                                                <img src="{{ asset('assets/img/car/icon/07.png') }}" alt="engine">
                                            </div>
                                            <div class="content">
                                                <h6>Engine:</h6>
                                                <p>{{ $car->engine_type ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="icon-details-main-items">
                                        <div class="icon-items">
                                            <div class="icon">
                                                <img src="{{ asset('assets/img/car/door.svg') }}" alt="passengers">
                                            </div>
                                            <div class="content">
                                                <h6>Passengers:</h6>
                                                <p>{{ $car->number_of_seats ?? 'N/A' }} Seats</p>
                                            </div>
                                        </div>
                                        <div class="icon-items">
                                            <div class="icon">
                                                <img src="{{ asset('assets/img/car/seat.svg') }}" alt="doors">
                                            </div>
                                            <div class="content">
                                                <h6>Doors:</h6>
                                                <p>4 Doors</p>
                                            </div>
                                        </div>
                                        <div class="icon-items">
                                            <div class="icon">
                                                <img src="{{ asset('assets/img/car/automatic.svg') }}" alt="transmission">
                                            </div>
                                            <div class="content">
                                                <h6>Transmission:</h6>
                                                <p>{{ $car->gearbox ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                        <div class="icon-items">
                                            <div class="icon">
                                                <img src="{{ asset('assets/img/car/petrol.svg') }}" alt="fuel type">
                                            </div>
                                            <div class="content">
                                                <h6>Fuel:</h6>
                                                <p>{{ $car->fuel_type ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="price-table-area">
                                    <h6>Table Price <span>( by day of the week )</span></h6>
                                    <div class="price-table-items section-bg">
                                        <p>Monday</p>
                                        <p>MAD{{ number_format($car->daily_rate ?? 70, 2) }}</p>
                                    </div>
                                    <div class="price-table-items">
                                        <p>Tuesday</p>
                                        <p>MAD{{ number_format($car->daily_rate ?? 70, 2) }}</p>
                                    </div>
                                    <div class="price-table-items section-bg">
                                        <p>Wednesday</p>
                                        <p>MAD{{ number_format($car->daily_rate ?? 70, 2) }}</p>
                                    </div>
                                    <div class="price-table-items">
                                        <p>Thursday</p>
                                        <p>MAD{{ number_format($car->daily_rate ?? 70, 2) }}</p>
                                    </div>
                                    <div class="price-table-items section-bg">
                                        <p>Friday</p>
                                        <p>MAD{{ number_format($car->daily_rate ?? 70, 2) }}</p>
                                    </div>
                                    <div class="price-table-items">
                                        <p>Saturday</p>
                                        <p>MAD{{ number_format($car->daily_rate ?? 70, 2) }}</p>
                                    </div>
                                    <div class="price-table-items section-bg">
                                        <p>Sunday</p>
                                        <p>MAD{{ number_format($car->daily_rate ?? 70, 2) }}</p>
                                    </div>
                                </div>
                                
                                @if($car->document)
                                <div class="car-video mt-5">
                                    <img src="{{ asset('assets/img/car/car-details-2.jpg') }}" alt="video thumbnail" class="img-fluid">
                                    <div class="video-box">
                                        <a href="{{ $car->document }}" class="video-btn ripple video-popup">
                                            <i class="fa-solid fa-play"></i>
                                        </a>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        
         <!-- Booking Form -->
        <div class="car-booking-items mt-5">
            <div class="booking-header">
                <h3>Request for Booking</h3>
                <p>Send your requirement to us. We will check email and contact you soon.</p>
            </div>
            
            <form action="{{ route('booking.store_request') }}" method="POST" class="contact-form-items" id="bookingForm">
            @csrf
            <input type="hidden" name="vehicle_id" value="{{ $car->id }}">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="form-clt">
                        <label class="label-text">Your Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required>
                        @error('name')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-clt">
                        <label class="label-text">Email *</label>
                        <input type="email" name="email" value="{{ old('email') }}" required>
                        @error('email')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-clt">
                        <label class="label-text">Phone Number *</label>
                        <input type="text" name="phone_number" value="{{ old('phone_number') }}" required>
                        @error('phone_number')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-clt">
                        <label class="label-text">Company Name (Optional)</label>
                        <input type="text" name="company_name" value="{{ old('company_name') }}">
                        @error('company_name')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-lg-6">
                <div class="form-clt">
                    <label class="label-text">City *</label>
                    <div class="category-oneadjust">
                        <select name="city" class="category" required>
                            <option value="">Select City</option>
                            @foreach($places as $place)
                                <option value="{{ $place->city }}" {{ old('city') == $place->city ? 'selected' : '' }}>
                                    {{ $place->city }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('city')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
            </div>

                <div class="col-lg-6">
                    <div class="form-clt">
                        <label class="label-text">Pick-up Location *</label>
                        <div class="category-oneadjust">
                            <select name="pickup_address" class="category">
                                <option value="">Select Location</option>
                                @foreach($places as $place)
                                    <option value="{{ $place->id }}" {{ old('pickup_address') == $place->id ? 'selected' : '' }}>
                                        {{ $place->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('pickup_address')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-clt">
                        <label class="label-text">Drop-off Address *</label>
                        <div class="category-oneadjust">
                            <select name="drop_off_address" class="category">
                                <option value="">Select Location</option>
                                @foreach($places as $place)
                                    <option value="{{ $place->id }}" {{ old('drop_off_address') == $place->id ? 'selected' : '' }}>
                                        {{ $place->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('drop_off_address')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>

                


                <div class="col-lg-6">
                    <div class="form-clt">
                        <label class="label-text">Pick-up Date *</label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}" required min="{{ date('Y-m-d') }}">
                        @error('start_date')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-clt">
                        <label class="label-text">Pick-up Time *</label>
                        <input type="time" name="start_time" value="{{ old('start_time') }}" required>
                        @error('start_time')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-clt">
                        <label class="label-text">Drop-off Date *</label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}" required min="{{ date('Y-m-d') }}">
                        @error('end_date')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-clt">
                        <label class="label-text">Drop-off Time *</label>
                        <input type="time" name="end_time" value="{{ old('end_time') }}" required>
                        @error('end_time')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-clt">
                        <label class="label-text">Need Driver?</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="driver" id="driver" value="1" {{ old('driver') ? 'checked' : '' }}>
                            <label class="form-check-label" for="driver">
                                Yes, I need a driver
                            </label>
                        </div>
                        @error('driver')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="form-clt">
                        <label class="label-text">Special Requests (Optional)</label>
                        <textarea name="notes" rows="3">{{ old('notes') }}</textarea>
                    </div>
                </div>
                <div class="col-lg-12">
                    <button class="theme-btn" type="submit" id="submitButton">Send Request</button>
                </div>
            </div>
        </form> 
                        </div>
                        
                    <!-- Reviews Section -->
                        <div class="comment-reviews mt-5">
                            <h3>2 Reviews</h3>
                            <div class="car-single-comment d-flex gap-4 pb-5">
                                <div class="image">
                                    <img src="{{ asset('assets/img/car/comment01.png') }}" alt="user" width="70" height="70" style="border-radius: 50%;">
                                </div>
                                <div class="content">
                                    <div class="head d-flex flex-wrap gap-3 align-items-center justify-content-between">
                                        <div class="con">
                                            <h4>Kevin Martin</h4>
                                        </div>
                                        <div class="star">
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                        </div>
                                    </div>
                                    <p class="mt-4">
                                        It has survived not only five centuries, but also the into electronic typesetting simply fee text aunchanged. It was popularised in the sheets containing lorem ipsum is simply free text.
                                    </p>
                                </div>
                            </div>
                            <div class="car-single-comment d-flex gap-4 pt-5 border-none">
                                <div class="image">
                                    <img src="{{ asset('assets/img/car/comment02.png') }}" alt="user" width="70" height="70" style="border-radius: 50%;">
                                </div>
                                <div class="content">
                                    <div class="head d-flex flex-wrap gap-3 align-items-center justify-content-between">
                                        <div class="con">
                                            <h4>Sarah Albert</h4>
                                        </div>
                                        <div class="star">
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                        </div>
                                    </div>
                                    <p class="mt-4">
                                        It has survived not only five centuries, but also the into electronic typesetting simply fee text aunchanged. It was popularised in the sheets containing lorem ipsum is simply free text.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <div class="car-list-sidebar">
                            <h4 class="title">Quick Booking</h4>
                            <form action="{{ route('booking.store_request') }}" method="POST">
                                @csrf
                                <input type="hidden" name="vehicle_id" value="{{ $car->id }}">
                                <div class="row g-4">
                                    <div class="col-lg-12">
                                        <div class="form-clt">
                                            <label class="label-text">Pick-up Location</label>
                                            <select name="pickup_address" required>
                                                <option value="">Select Location</option>
                                                <option value="Houston">Houston</option>
                                                <option value="Texas">Texas</option>
                                                <option value="New York">New York</option>
                                                <option value="Other">Other Location</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-clt">
                                            <label class="label-text">Drop-off Address</label>
                                            <input type="text" name="drop_off_address" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-clt">
                                            <label class="label-text">Pick-up Date</label>
                                            <input type="date" name="start_date" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-clt">
                                            <label class="label-text">Pick-up Time</label>
                                            <input type="time" name="start_time" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-clt">
                                            <label class="label-text">Drop-off Date</label>
                                            <input type="date" name="end_date" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-clt">
                                            <label class="label-text">Drop-off Time</label>
                                            <input type="time" name="end_time" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-clt">
                                            <button type="submit" class="theme-btn w-100">Book Now</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            
                            <div class="mt-5">
                                <h4>Need Help?</h4>
                                <p>Our team is here to help you with your booking</p>
                                <p><i class="fas fa-phone me-2"></i> +1 (800) 123-4567</p>
                                <p><i class="fas fa-envelope me-2"></i> support@carrental.com</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Similar Cars Section -->
    <section class="car-rentals-section-2 section-padding fix pt-0">
        <div class="container">
            <div class="section-title text-center">
                <span>Checkout our new cars</span>
                <h2>Similar Cars Available</h2>
            </div>
            <div class="row">
                @foreach($similarCars as $similarCar)
                <div class="col-xl-4 col-lg-6 col-md-6">
                    <div class="car-rentals-items">
                        <div class="car-image">
                            <img src="{{ asset('storage/' . $similarCar->picture) }}" alt="{{ $car->name ?? 'Car Image' }}">
                        </div>
                        <div class="car-content">                        
                            <div class="post-cat">{{ $similarCar->year_of_first_immatriculation ?? 'N/A' }} Model</div>
                            <div class="star">
                                @for($i = 0; $i < 5; $i++)
                                    <i class="fa-solid fa-star"></i>
                                @endfor
                                <span>2 Reviews</span>
                            </div>
                            <h4><a href="{{ route('client.details', $similarCar->id) }}" style="text-decoration: none">{{ $similarCar->name }}</a></h4>
                            <h6>${{ number_format($similarCar->daily_rate ?? 70, 2) }} <span>/ Day</span></h6>
                            <div class="icon-items">
                                <ul>
                                    <li>
                                        <img src="{{ asset('assets/img/car/seat.svg') }}" alt="seats" class="me-1">
                                        {{ $similarCar->number_of_seats ?? '6' }} Seats
                                    </li>
                                    <li>
                                        <img src="{{ asset('assets/img/car/door.svg') }}" alt="doors" class="me-1">
                                        N/A
                                    </li>
                                </ul>
                                <ul>
                                    <li>
                                        <img src="{{ asset('assets/img/car/automatic.svg') }}" alt="transmission" class="me-1">
                                        {{ $similarCar->gearbox ?? 'Automatic' }}
                                    </li>
                                    <li>
                                        <img src="{{ asset('assets/img/car/petrol.svg') }}" alt="fuel type" class="me-1">
                                        {{ $similarCar->fuel_type ?? 'Petrol' }}
                                    </li>
                                </ul>
                            </div>
                            {{-- <a href="{{ route('car.details', $similarCar->id) }}" class="theme-btn bg-color w-100 text-center">book now <i class="fa-solid fa-arrow-right ps-1"></i></a> --}}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    
    <script>
        // Show success message with SweetAlert if booking was successful
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Booking Request Sent!',
                text: '{{ session('success') }}',
                confirmButtonColor: '#4e5ee4',
            });
        @endif

        // Show error message with SweetAlert if there was an error
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '{{ session('error') }}',
                confirmButtonColor: '#4e5ee4',
            });
        @endif

        // Simple form validation
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    let valid = true;
                    const requiredFields = form.querySelectorAll('[required]');
                    
                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            valid = false;
                            field.style.borderColor = 'red';
                        } else {
                            field.style.borderColor = '';
                        }
                    });
                    
                    if (!valid) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'error',
                            title: 'Missing Information',
                            text: 'Please fill all required fields',
                            confirmButtonColor: '#4e5ee4',
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>