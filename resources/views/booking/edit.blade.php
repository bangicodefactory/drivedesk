@extends('layouts.app')
@section('page-title')
    {{ __('Inspection') }}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">
                <h1>{{ __('Dashboard') }}</h1>
            </a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('booking.index') }}">
                {{ __('Booking') }}
            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">
                {{ __('Edit') }}
            </a>
        </li>
    </ul>
@endsection
@section('card-action-btn')
@endsection
@section('content')
    {{ Form::model($booking, ['route' => ['booking.update', $booking->id], 'method' => 'PUT']) }}
    <div class="row">
        <div class="col-md-12 col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <input type="hidden" name="booking_id" id="booking_id" value="{{ $booking->id }}">
                        <input type="hidden" name="details" id="details" value="{{ $booking->details }}">
                        <div class="form-group col-md-4 col-lg-4">
                            {{ Form::label('start_date_time', __('Start Date Time'), ['class' => 'form-label']) }}
                            {{ Form::text('start_date_time', null, ['class' => 'form-control start_date_time', 'placeholder' => __('Select Start Date & Time')]) }}
                        </div>
                        <div class="form-group col-md-4 col-lg-4">
                            {{ Form::label('end_date_time', __('End Date Time'), ['class' => 'form-label']) }}
                            {{ Form::text('end_date_time', null, ['class' => 'form-control end_date_time', 'placeholder' => __('Select End Date & Time')]) }}
                        </div>
                        <div class="form-group col-md-4 col-lg-4">
                            {{ Form::label('vehicle', __('Vehicle'), ['class' => 'form-label']) }}

                            <select name="vehicle" id="vehicle" class="form-control basic-select" required>
                                <option value="">{{ __('Select Vehicle') }}</option>
                                @foreach ($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}"
                                        {{ $booking->vehicle == $vehicle->id ? 'selected' : '' }}>
                                        {{ $vehicle->name . ' - ' . $vehicle->license_plate }}</option>
                                @endforeach
                            </select>

                        </div>
                        <div class="form-group col-md-4 col-lg-4">
                            {{ Form::label('driver', __('Driver'), ['class' => 'form-label']) }}
                            {!! Form::select('driver', $drivers, null, ['class' => 'form-control hidesearch ']) !!}
                            <span class="float-end"> <a class=" customModal" href="#" data-size="lg"
                                    data-url="{{ route('driver.new.create') }}"
                                    data-title="{{ __('Create Driver') }}">{{ __('Create New Driver') }}</a></span>
                        </div>
                        <div class="form-group col-md-4 col-lg-4">
                            {{ Form::label('pickup_address', __('Pickup Address'), ['class' => 'form-label']) }}
                            <select name="pickup_address" id="pickup_address" class="form-control basic-select" required>
                                <option value="">{{ __('Select Pickup Address') }}</option>
                                @foreach ($places as $place)
                                    <option value="{{ $place->id }}"
                                        {{ $booking->pickup_address == $place->id ? 'selected' : '' }}>{{ $place->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-4 col-lg-4">
                            {{ Form::label('drop_off_address', __('Drop Off Address'), ['class' => 'form-label']) }}
                            <select name="drop_off_address" id="drop_off_address" class="form-control basic-select"
                                required>
                                <option value="">{{ __('Select Drop Off Address') }}</option>
                                @foreach ($places as $place)
                                    <option value="{{ $place->id }}"
                                        {{ $booking->drop_off_address == $place->id ? 'selected' : '' }}>
                                        {{ $place->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-4 col-lg-4">
                            {{ Form::label('addon', __('Addon'), ['class' => 'form-label']) }}
                            {!! Form::select('addon[]', $addon, !empty($booking->addon) ? explode(',', $booking->addon) : null, [
                                'class' => 'form-control hidesearch addon',
                                'multiple',
                            ]) !!}
                        </div>

                        <div class="form-group col-md-4 col-lg-4">
                            {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                            {!! Form::select('status', $status, null, ['class' => 'form-control hidesearch ']) !!}
                        </div>
                        <input type="hidden" name="amount" id="amount" value="{{ $booking->amount }}">
                        <div class="form-group col-md-4 col-lg-4">
                            {{ Form::label('notes', __('Notes'), ['class' => 'form-label']) }}
                            {{ Form::textarea('notes', null, ['class' => 'form-control', 'placeholder' => __('Enter notes'), 'rows' => 2]) }}
                        </div>
                        @php
                            $details = json_decode($booking->details);
                        @endphp
                        <div class="col-md-6 col-lg-6 detail_div">
                            <table class="display dataTable cell-border">
                                <tbody class="text-center" id="detail_table">
                                    <tr>
                                        <td> {{ __('Duration') }}</td>
                                        <td class="duration">
                                            {{ $details->considerDays . ' * ' . $booking->vehicleDetails()->daily_rate . ' = ' . priceFormat($details->totalRate) }}
                                        </td>
                                    </tr>
                                </tbody>
                                <tbody class="text-center" id="addonData">
                                    @foreach ($booking->addons() as $addon)
                                        <tr>
                                            <td>{{ $addon->name }}</td>
                                            <td>
                                                {{ priceFormat($addon->price) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tbody class="text-center" id="placeData"></tbody>
                                <tbody class="text-center" id="pickupPlace">
                                    <tr>
                                        <td>{{ !empty($booking->pickupAddress) ? $booking->pickupAddress->name : '-' }}
                                        </td>
                                        <td>{{ priceFormat($booking->pickupAddress->price) }}</td>
                                    </tr>
                                </tbody>
                                <tbody class="text-center" id="dropPlace">
                                    <tr>
                                        <td>{{ !empty($booking->dropOffAddress) ? $booking->dropOffAddress->name : '-' }}
                                        </td>
                                        <td>{{ priceFormat($booking->dropOffAddress->price) }}</td>
                                    </tr>
                                </tbody>
                                <tbody class="text-center">
                                    <tr>
                                        <td><b class="h6">{{ __('Total Amount') }}</b></td>
                                        <td><b class="h6"> <span name="amount"
                                                    id="totalAmount">{{ priceFormat($booking->amount) }}</span></b></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row text-end">
        <div class="col-md-12 col-lg-12">
            {{ Form::submit(__('Update'), ['class' => 'btn btn-primary ml-10']) }}
        </div>
    </div>
    {{ Form::close() }}
@endsection
@push('script-page')
    <script>
        $(document).ready(function() {
            var today = new Date();
            if ($('.start_date_time').length > 0) {
                $('.start_date_time').datetimepicker({
                    step: 15,
                    minDate: today,
                    onClose: function(current_time, $input) {
                        var start_date_time = $input.val();
                        var end_date_time_picker = $('.end_date_time');

                        if (start_date_time) {
                            end_date_time_picker.datetimepicker('setOptions', {
                                minDate: new Date(start_date_time)
                            });
                            end_date_time_picker.prop('disabled', false);
                        } else {
                            end_date_time_picker.prop('disabled', true);
                        }
                    }
                });
            }

            if ($('.end_date_time').length > 0) {
                $('.end_date_time').datetimepicker({
                    step: 15,
                    minDate: today
                });
            }
        });
    </script>
    <script>
        $(document).on('click', '.create_btn', function(e) {
            var formData = $("form").serialize();
            $.ajax({
                url: "{{ route('driver.store') }}",
                type: "POST",
                data: formData,
                success: function(result) {
                    var response = JSON.parse(result);
                    if (response.status && response.data) {
                        var selectElement = $("#driver");
                        selectElement.empty();
                        var keys = Object.keys(response.data).sort(function(a, b) {
                            return b - a;
                        });
                        keys.forEach(function(key) {
                            var option = $("<option></option>").attr("value", key).text(response
                                .data[key]);
                            selectElement.append(option);
                        });
                    }
                    $("#customModal").modal('hide');
                },
                error: function(result) {
                    toastrs('error', result, 'error')
                }
            });
        });
    </script>
    <script>
        $(document).on('change', '#start_date_time,#end_date_time', function(e) {
            var start_date_time = $("#start_date_time").val();
            var end_date_time = $("#end_date_time").val();
            var booking_id = $("#booking_id").val();

            if (start_date_time != '' && end_date_time != '') {
                $.ajax({
                    url: "{{ route('available.vehicle') }}",
                    type: "GET",
                    data: {
                        start_date_time: start_date_time,
                        end_date_time: end_date_time,
                        booking_id: booking_id
                    },
                    success: function(result) {
                        var response = JSON.parse(result);

                        var selectElement = $("#vehicle");
                        selectElement.empty();
                        var keys = Object.keys(response).sort(function(a, b) {
                            return b - a;
                        });
                        keys.forEach(function(key) {
                            var option = $("<option></option>").attr("value", key).text(
                                response[key]);
                            selectElement.append(option);
                        });
                        $('#vehicle').trigger('change');
                    },
                    error: function(result) {
                        toastrs('error', result, 'error')
                    }
                });
            }


        });

        $(document).on('change', '#vehicle', function(e) {
            var vahicle_id = $("#vehicle").val();
            var start_date_time = $("#start_date_time").val();
            var end_date_time = $("#end_date_time").val();
            var addons = $(".addon").val();
            var pickup_place = $("#pickup_address").val();
            var drop_off_place = $("#drop_off_address").val();

            if (vahicle_id != '' && start_date_time != '' && end_date_time != '') {
                $.ajax({
                    url: "{{ route('vehicle.rate.calculation') }}",
                    type: "GET",
                    data: {
                        vahicle_id: vahicle_id,
                        start_date_time: start_date_time,
                        end_date_time: end_date_time,
                        addons: addons,
                        pickup_place: pickup_place,
                        drop_off_place: drop_off_place,
                    },
                    success: function(result) {
                        $('.detail_div').removeClass('d-none');
                        var response = JSON.parse(result);
                        var totalRate = parseFloat(response['totalRate']) || 0;
                        var addonAmount = parseFloat(response['addonAmount']) || 0;
                        var placeAmount = parseFloat(response['placeAmount']) || 0;
                        var sum = totalRate + addonAmount + placeAmount;
                        $('#amount').val(sum);
                        $('#details').val(result);

                        $('.duration').html(response['duration']);
                        $('#addonData').html(response['specificAddonCalculation']);
                        $('#totalAmount').html(sum);
                    },
                    error: function(result) {
                        toastrs('error', result, 'error')
                    }
                });
            }
        });
    </script>
    <script>
        $(document).on('change', '.addon', function(e) {
            var addons = $(this).val();
            var vahicle_id = $("#vehicle").val();
            var start_date_time = $("#start_date_time").val();
            var end_date_time = $("#end_date_time").val();
            var pickup_place = $("#pickup_address").val();
            var drop_off_place = $("#drop_off_address").val();
            $.ajax({
                url: "{{ route('addon.rate.calculation') }}",
                type: "GET",
                data: {
                    addons: addons,
                    vahicle_id: vahicle_id,
                    start_date_time: start_date_time,
                    end_date_time: end_date_time,
                    pickup_place: pickup_place,
                    drop_off_place: drop_off_place,
                },
                success: function(result) {
                    var response = JSON.parse(result);
                    var totalRate = parseFloat(response['totalRate']) || 0;
                    var addonAmount = parseFloat(response['addonAmount']) || 0;
                    var placeAmount = parseFloat(response['placeAmount']) || 0;
                    var sum = totalRate + addonAmount + placeAmount;
                    $('#amount').val(sum);
                    $('#details').val(result);
                    $('#addonData').html(response['specificAddonCalculation']);
                    $('#totalAmount').html(sum);
                },
                error: function(result) {
                    toastrs('error', result, 'error')
                }
            });
        });

        $(document).on('change', '#pickup_address,#drop_off_address', function(e) {
            var pickup_place = $("#pickup_address").val();
            var drop_off_place = $("#drop_off_address").val();
            var vehicle_id = $("#vehicle").val();
            var start_date_time = $("#start_date_time").val();
            var end_date_time = $("#end_date_time").val();
            var addons = $(".addon").val();

            if (pickup_place != '' || drop_off_place != '') {
                $.ajax({
                    url: "{{ route('place.rate.calculation') }}",
                    type: "GET",
                    data: {
                        vahicle_id: vehicle_id,
                        start_date_time: start_date_time,
                        end_date_time: end_date_time,
                        addons: addons,
                        pickup_place: pickup_place,
                        drop_off_place: drop_off_place,
                    },
                    success: function(result) {
                        var response = JSON.parse(result);
                        var totalRate = parseFloat(response['totalRate']) || 0;
                        var addonAmount = parseFloat(response['addonAmount']) || 0;
                        var placeAmount = parseFloat(response['placeAmount']) || 0;
                        var sum = totalRate + addonAmount + placeAmount;
                        let str = sum;
                        let amo = str.replace("$", "");
                        $('#amount').val(amo);
                        $('#details').val(result);
                        $('#pickupPlace').html(response['pickup_place']);
                        $('#dropPlace').html(response['drop_place']);
                        $('#totalAmount').html(sum);
                    },
                    error: function(result) {
                        toastrs('error', result, 'error')
                    }
                });
            }
        });
    </script>
@endpush
