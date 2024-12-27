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
                <?php echo e(__('Create')); ?>

            </a>
        </li>
    </ul>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('card-action-btn'); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <?php echo e(Form::open(['url' => 'booking', 'method' => 'post', 'id' => 'myForm'])); ?>

    <div class="row">
        <div class="col-md-12 col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <input type="hidden" name="details" id="details">
                        <div class="form-group col-md-4 col-lg-4">
                            <?php echo e(Form::label('start_date_time', __('Start Date Time'), ['class' => 'form-label'])); ?>

                            <?php echo e(Form::text('start_date_time', null, ['class' => 'form-control start_date_time', 'placeholder' => __('Select Start Date & Time'), 'required' => 'required'])); ?>

                        </div>
                        <div class="form-group col-md-4 col-lg-4">
                            <?php echo e(Form::label('end_date_time', __('End Date Time'), ['class' => 'form-label'])); ?>

                            <?php echo e(Form::text('end_date_time', null, ['class' => 'form-control end_date_time', 'placeholder' => __('Select End Date & Time'), 'required' => 'required'])); ?>

                        </div>
                        <div class="form-group col-md-4 col-lg-4">
                            <?php echo e(Form::label('vehicle', __('Vehicle'), ['class' => 'form-label'])); ?>

                            <select name="vehicle" id="vehicle" class="form-control basic-select" required>
                                <option value=""><?php echo e(__('Select Vehicle')); ?></option>
                                <?php $__currentLoopData = $vehicles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vehicle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($vehicle->id); ?>">
                                        <?php echo e($vehicle->name . ' - ' . $vehicle->license_plate); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="form-group col-md-4 col-lg-4">
                            <?php echo e(Form::label('driver', __('Driver'), ['class' => 'form-label'])); ?>

                            <?php echo Form::select('driver', $drivers, null, ['class' => 'form-control hidesearch ', 'required' => 'required']); ?>

                            <span class="float-end"> <a class=" customModal" href="#" data-size="lg"
                                    data-url="<?php echo e(route('driver.new.create')); ?>"
                                    data-title="<?php echo e(__('Create Driver')); ?>"><?php echo e(__('Create New Driver')); ?></a></span>
                        </div>

                        <div class="form-group col-md-4 col-lg-4">
                            <?php echo e(Form::label('pickup_address', __('Pickup Address'), ['class' => 'form-label'])); ?>

                            <select name="pickup_address" id="pickup_address" class="form-control basic-select" required>
                                <option value=""><?php echo e(__('Select Pickup Address')); ?></option>
                                <?php $__currentLoopData = $places; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $place): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($place->id); ?>"><?php echo e($place->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="form-group col-md-4 col-lg-4">
                            <?php echo e(Form::label('drop_off_address', __('Drop Off Address'), ['class' => 'form-label'])); ?>

                            <select name="drop_off_address" id="drop_off_address" class="form-control basic-select"
                                required>
                                <option value=""><?php echo e(__('Select Drop Off Address')); ?></option>
                                <?php $__currentLoopData = $places; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $place): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($place->id); ?>"><?php echo e($place->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="form-group col-md-4 col-lg-4">
                            <?php echo e(Form::label('addon', __('Addon'), ['class' => 'form-label'])); ?>

                            <?php echo Form::select('addon[]', $addon, null, ['class' => 'form-control hidesearch addon', 'multiple']); ?>

                        </div>

                        <div class="form-group col-md-4 col-lg-4">
                            <?php echo e(Form::label('status', __('Status'), ['class' => 'form-label'])); ?>

                            <?php echo Form::select('status', $status, null, ['class' => 'form-control hidesearch ', 'required' => 'required']); ?>

                        </div>
                        <input type="hidden" name="amount" id="amount">
                        <div class="form-group col-md-4 col-lg-4">
                            <?php echo e(Form::label('notes', __('Notes'), ['class' => 'form-label'])); ?>

                            <?php echo e(Form::textarea('notes', null, ['class' => 'form-control', 'placeholder' => __('Enter notes'), 'rows' => 2])); ?>

                        </div>

                        <div class="col-md-6 col-lg-6 detail_div d-none">
                            <table class="display dataTable cell-border">
                                <tbody class="text-center" id="detail_table">
                                    <tr>
                                        <td> <?php echo e(__('Duration')); ?></td>
                                        <td class="duration"></td>
                                    </tr>
                                </tbody>
                                <tbody class="text-center" id="addonData"></tbody>
                                <tbody class="text-center" id="placeData"></tbody>
                                <tbody class="text-center" id="pickupPlace"></tbody>
                                <tbody class="text-center" id="dropPlace"></tbody>
                                <tbody class="text-center">
                                    <tr>
                                        <td><b class="h6"><?php echo e(__('Total Amount')); ?></b></td>
                                        <td><b class="h6"> <span id="totalAmount"></span></b></td>
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
            <?php echo e(Form::submit(__('Create'), ['class' => 'btn btn-primary ml-10'])); ?>

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
                }).prop('disabled', true);
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
                    if(response.status==false){
                        toastrs('error', response.data, 'error');
                        $("#customModal").modal('hide');
                        return false;
                    }

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
            start_datetime = start_date_time;
            if (start_date_time != '' && end_date_time != '') {
                $.ajax({
                    url: "<?php echo e(route('available.vehicle')); ?>",
                    type: "GET",
                    data: {
                        start_date_time: start_date_time,
                        end_date_time: end_date_time,
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
            var vehicle_id = $("#vehicle").val();
            var start_date_time = $("#start_date_time").val();
            var end_date_time = $("#end_date_time").val();
            var addons = $(".addon").val();
            var pickup_place = $("#pickup_address").val();
            var drop_off_place = $("#drop_off_address").val();
            if (vehicle_id != '' && start_date_time != '' && end_date_time != '') {
                $.ajax({
                    url: "<?php echo e(route('vehicle.rate.calculation')); ?>",
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
        $(document).on('change', '#pickup_address,#drop_off_address', function(e) {
            var pickup_place = $("#pickup_address").val();
            var drop_off_place = $("#drop_off_address").val();
            var vehicle_id = $("#vehicle").val();
            var start_date_time = $("#start_date_time").val();
            var end_date_time = $("#end_date_time").val();
            var addons = $(".addon").val();

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
                url: "<?php echo e(route('addon.rate.calculation')); ?>",
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
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/carrent/resources/views/booking/create.blade.php ENDPATH**/ ?>