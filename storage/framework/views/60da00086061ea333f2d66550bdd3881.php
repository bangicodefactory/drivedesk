<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Inspection')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?php echo e(route('dashboard')); ?>">
                <h1><?php echo e(__('Dashboard')); ?></h1>
            </a>
        </li>
        <li class="breadcrumb-item">
            <a href="<?php echo e(route('booking.index')); ?>">
                <?php echo e(__('Booking')); ?>

            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">
                <?php echo e(__('Edit')); ?>

            </a>
        </li>
    </ul>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('card-action-btn'); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <?php echo e(Form::model($booking, ['route' => ['booking.update', $booking->id], 'method' => 'PUT'])); ?>

    <div class="row">
        <div class="col-md-12 col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <input type="hidden" name="booking_id" id="booking_id" value="<?php echo e($booking->id); ?>">
                        <input type="hidden" name="details" id="details" value="<?php echo e($booking->details); ?>">
                        <div class="form-group col-md-4 col-lg-4">
                            <?php echo e(Form::label('start_date_time', __('Start Date Time'), ['class' => 'form-label'])); ?>

                            <?php echo e(Form::text('start_date_time', null, ['class' => 'form-control start_date_time', 'placeholder' => __('Select Start Date & Time')])); ?>

                        </div>
                        <div class="form-group col-md-4 col-lg-4">
                            <?php echo e(Form::label('end_date_time', __('End Date Time'), ['class' => 'form-label'])); ?>

                            <?php echo e(Form::text('end_date_time', null, ['class' => 'form-control end_date_time', 'placeholder' => __('Select End Date & Time')])); ?>

                        </div>
                        <div class="form-group col-md-4 col-lg-4">
                            <?php echo e(Form::label('vehicle', __('Vehicle'), ['class' => 'form-label'])); ?>


                            <select name="vehicle" id="vehicle" class="form-control basic-select" required>
                                <option value=""><?php echo e(__('Select Vehicle')); ?></option>
                                <?php $__currentLoopData = $vehicles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vehicle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($vehicle->id); ?>"
                                        <?php echo e($booking->vehicle == $vehicle->id ? 'selected' : ''); ?>>
                                        <?php echo e($vehicle->name . ' - ' . $vehicle->license_plate); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>

                        </div>
                        <div class="form-group col-md-4 col-lg-4">
                            <?php echo e(Form::label('driver', __('Driver'), ['class' => 'form-label'])); ?>

                            <?php echo Form::select('driver', $drivers, null, ['class' => 'form-control  basic-select']); ?>

                            <span class="float-end"> <a class=" customModal" href="#" data-size="lg"
                                    data-url="<?php echo e(route('driver.new.create')); ?>"
                                    data-title="<?php echo e(__('Create Driver')); ?>"><?php echo e(__('Create New Driver')); ?></a></span>
                        </div>
                        <div class="form-group col-md-4 col-lg-4">
                            <?php echo e(Form::label('pickup_address', __('Pickup Address'), ['class' => 'form-label'])); ?>

                            <select name="pickup_address" id="pickup_address" class="form-control basic-select" required>
                                <option value=""><?php echo e(__('Select Pickup Address')); ?></option>
                                <?php $__currentLoopData = $places; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $place): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($place->id); ?>"
                                        <?php echo e($booking->pickup_address == $place->id ? 'selected' : ''); ?>><?php echo e($place->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="form-group col-md-4 col-lg-4">
                            <?php echo e(Form::label('drop_off_address', __('Drop Off Address'), ['class' => 'form-label'])); ?>

                            <select name="drop_off_address" id="drop_off_address" class="form-control basic-select"
                                required>
                                <option value=""><?php echo e(__('Select Drop Off Address')); ?></option>
                                <?php $__currentLoopData = $places; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $place): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($place->id); ?>"
                                        <?php echo e($booking->drop_off_address == $place->id ? 'selected' : ''); ?>>
                                        <?php echo e($place->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="form-group col-md-4 col-lg-4">
                            <?php echo e(Form::label('addon', __('Addon'), ['class' => 'form-label'])); ?>

                            <?php echo Form::select('addon[]', $addon, !empty($booking->addon) ? explode(',', $booking->addon) : null, [
                                'class' => 'form-control hidesearch addon',
                                'multiple',
                            ]); ?>

                        </div>
                        

                        
                        
                        <div class="form-group col-md-4 col-lg-4">
                            <?php echo e(Form::label('daily_price', __('Price a day'), ['class' => 'form-label'])); ?>

                            <?php echo e(Form::number('daily_price', !empty($booking->daily_price_final) ? $booking->daily_price_final : $booking->vehicleDetails()->daily_rate, [
                                'class' => 'form-control',
                                'id' => 'daily_price',
                                'step' => 'any',
                                'min' => '0',
                            ])); ?>

                        </div>

                        <div class="form-group col-md-4 col-lg-4">
                            <?php echo e(Form::label('status', __('Status'), ['class' => 'form-label'])); ?>

                            <?php echo Form::select('status', $status, null, ['class' => 'form-control hidesearch ']); ?>

                        </div>
                        <input type="hidden" name="amount" id="amount" value="<?php echo e($booking->amount); ?>">
                        <div class="form-group col-md-4 col-lg-4">
                            <?php echo e(Form::label('notes', __('Notes'), ['class' => 'form-label'])); ?>

                            <?php echo e(Form::textarea('notes', null, ['class' => 'form-control', 'placeholder' => __('Enter notes'), 'rows' => 2])); ?>

                        </div>
                        <?php
                            $details = json_decode($booking->details);
                        ?>
                        <div class="col-md-6 col-lg-6 detail_div">
                            <table class="display dataTable cell-border">
                                <tbody class="text-center" id="detail_table">
                                    <tr>
                                        <td> <?php echo e(__('Duration')); ?></td>
                                        <td class="duration">
                                            
                                            <?php echo e($details->considerDays . ' * ' . $booking->daily_price_final . ' = ' . priceFormat($details->totalRate)); ?>

                                        </td>
                                    </tr>
                                </tbody>
                                <tbody class="text-center" id="addonData">
                                    <?php $__currentLoopData = $booking->addons(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $addon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($addon->name); ?></td>
                                            <td>
                                                <?php echo e(priceFormat($addon->price)); ?>

                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                                <tbody class="text-center" id="placeData"></tbody>
                                <tbody class="text-center" id="pickupPlace">
                                    <tr>
                                        <td><?php echo e(!empty($booking->pickupAddress) ? $booking->pickupAddress->name : '-'); ?>

                                        </td>
                                        <td><?php echo e(priceFormat($booking->pickupAddress->price)); ?></td>
                                    </tr>
                                </tbody>
                                <tbody class="text-center" id="dropPlace">
                                    <tr>
                                        <td><?php echo e(!empty($booking->dropOffAddress) ? $booking->dropOffAddress->name : '-'); ?>

                                        </td>
                                        <td><?php echo e(priceFormat($booking->dropOffAddress->price)); ?></td>
                                    </tr>
                                </tbody>
                                
                                <tbody class="text-center">
                                    <tr>
                                        <td><b class="h6"><?php echo e(__('Total Amount')); ?></b></td>
                                        <td><b class="h6"> <span name="amount"
                                                    id="totalAmount"><?php echo e(priceFormat($booking->amount)); ?></span></b></td>
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
            <?php echo e(Form::submit(__('Update'), ['class' => 'btn btn-primary ml-10'])); ?>

        </div>
    </div>
    <?php echo e(Form::close()); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startPush('script-page'); ?>
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
                url: "<?php echo e(route('driver.store')); ?>",
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
    
    // Store the currently selected vehicle and daily price
    var currentlySelectedVehicle = $("#vehicle").val();
    var current_daily_price = $("#daily_price").val();

    if (start_date_time != '' && end_date_time != '') {
        $.ajax({
            url: "<?php echo e(route('available.vehicle')); ?>",
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
                
                // Add default option
                selectElement.append('<option value=""><?php echo e(__("Select Vehicle")); ?></option>');
                
                var keys = Object.keys(response).sort(function(a, b) {
                    return b - a;
                });
                
                var vehicleStillAvailable = false;
                
                keys.forEach(function(key) {
                    var option = $("<option></option>").attr("value", key).text(response[key]);
                    selectElement.append(option);
                    
                    // Check if the previously selected vehicle is still available
                    if (key == currentlySelectedVehicle) {
                        vehicleStillAvailable = true;
                    }
                });
                
                // Restore selection if the vehicle is still available
                if (vehicleStillAvailable && currentlySelectedVehicle) {
                    selectElement.val(currentlySelectedVehicle);
                    
                    // Preserve the daily price and trigger recalculation
                    $("#daily_price").val(current_daily_price);
                    
                    // Trigger change event to recalculate with preserved price
                    $('#vehicle').trigger('change');
                } else if (currentlySelectedVehicle) {
                    // Show warning if previously selected vehicle is no longer available
                    toastrs('warning', 'The previously selected vehicle is no longer available for the new time range. Please select another vehicle.', 'warning');
                }
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
    
    // Store the current daily price (custom price set by user)
    var current_daily_price = $("#daily_price").val();
    var use_custom_price = current_daily_price && current_daily_price != '';

    if (vahicle_id != '' && start_date_time != '' && end_date_time != '') {
        $.ajax({
            url: "<?php echo e(route('vehicle.rate.calculation')); ?>",
            type: "GET",
            data: {
                vahicle_id: vahicle_id,
                start_date_time: start_date_time,
                end_date_time: end_date_time,
                addons: addons,
                pickup_place: pickup_place,
                drop_off_place: drop_off_place,
                daily_price: use_custom_price ? current_daily_price : null, // Pass custom price if exists
                daychange: use_custom_price ? 1 : 0 // Flag to indicate custom price usage
            },
            success: function(result) {
                $('.detail_div').removeClass('d-none');
                var response = JSON.parse(result);
                var totalRate = parseFloat(response['totalRate']) || 0;
                var addonAmount = parseFloat(response['addonAmount']) || 0;
                var placeAmount = parseFloat(response['placeAmount']) || 0;
                var daily_price_data = parseFloat(response['daily_price']) || 0;
                var sum = totalRate + addonAmount + placeAmount;

                // Only update daily price if we're not using a custom price
                if (!use_custom_price) {
                    $('#daily_price').val(daily_price_data);
                }
                
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
    
    // Use the current daily price (preserve custom pricing)
    var daily_price = $("#daily_price").val();
    
    $.ajax({
        url: "<?php echo e(route('addon.rate.calculation')); ?>",
        type: "GET",
        data: {
            addons: addons,
            vahicle_id: vahicle_id,
            start_date_time: start_date_time,
            end_date_time: end_date_time,
            pickup_place: pickup_place,
            drop_off_place: drop_off_place,
            daily_price: daily_price, // Pass the current daily price
            daychange: 1 // Indicate we're using custom pricing
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
    
    // Use the current daily price (preserve custom pricing)
    var daily_price = $("#daily_price").val();
    var daychange = 1;

    if (pickup_place != '' || drop_off_place != '') {
        $.ajax({
            url: "<?php echo e(route('place.rate.calculation')); ?>",
            type: "GET",
            data: {
                vahicle_id: vehicle_id,
                start_date_time: start_date_time,
                end_date_time: end_date_time,
                addons: addons,
                pickup_place: pickup_place,
                drop_off_place: drop_off_place,
                daily_price: daily_price, // Use current daily price
                daychange: daychange,
            },
            success: function(result) {
                var response = JSON.parse(result);
                var totalRate = parseFloat(response['totalRate']) || 0;
                var addonAmount = parseFloat(response['addonAmount']) || 0;
                var placeAmount = parseFloat(response['placeAmount']) || 0;
                var sum = totalRate + addonAmount + placeAmount;

                $('#amount').val(sum);
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

        $(document).on('change', '#daily_price', function(e) {
            var addons = $(".addon").val();
            var vahicle_id = $("#vehicle").val();
            var start_date_time = $("#start_date_time").val();
            var end_date_time = $("#end_date_time").val();

            var pickup_place = $("#pickup_address").val();
            var drop_off_place = $("#drop_off_address").val();

            console.log('pickup_place: ' + pickup_place);
            console.log('drop_off_place: ' + drop_off_place);

            var daily_price = $("#daily_price").val();
            var daychange = 1;
            $.ajax({
                url: "<?php echo e(route('vehicle.rate.calculation')); ?>",
                type: "GET",
                data: {
                    addons: addons,
                    vahicle_id: vahicle_id,
                    start_date_time: start_date_time,
                    end_date_time: end_date_time,
                    pickup_place: pickup_place,
                    drop_off_place: drop_off_place,
                    daily_price: daily_price,
                    daychange: daychange,
                },
                success: function(result) {
                    var response = JSON.parse(result);
                    var totalRate = parseFloat(response['totalRate']) || 0;
                    var addonAmount = parseFloat(response['addonAmount']) || 0;
                    var placeAmount = parseFloat(response['placeAmount']) || 0;
                    var sum = totalRate + addonAmount + placeAmount;

                    var discountAmount = parseFloat($('#discount').val()) || 0;
                    var finalSum = sum - discountAmount;

                    
                    $('#amount').val(finalSum);
                    $('#details').val(result);
                    $('#addonData').html(response['specificAddonCalculation']);
                    $('#totalAmount').html(finalSum);

                    $('#discountPlace').html(discountAmount + ' Dh');

                    $('.duration').html(response['duration']);

                    console.log(response);
                    console.log(response['duration']);
                    console.log('Daily Price new :' + daily_price);
                    console.log('Addon Amout new: ' + addonAmount);
                    console.log('placeAmount:'  + placeAmount);
                },
                error: function(result) {
                    toastrs('error', result, 'error')
                }
            });
    });

var userChangedDailyPrice = false;

$(document).on('input', '#daily_price', function(e) {
    userChangedDailyPrice = true;
});

// Reset the flag when vehicle changes (in case user wants to use the new vehicle's default price)
$(document).on('change', '#vehicle', function(e) {
    // Only reset if this is a new vehicle selection, not a programmatic restore
    if (!$(this).data('restoring')) {
        userChangedDailyPrice = false;
    }
});

    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/car_directonderweg/resources/views/booking/edit.blade.php ENDPATH**/ ?>