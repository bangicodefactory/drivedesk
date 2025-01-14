<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Booking')); ?>

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
                <?php echo e(bookingPrefix() . $booking->booking_id); ?> <?php echo e(__('Details')); ?>

            </a>
        </li>
    </ul>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('card-action-btn'); ?>
    <?php if(Gate::check('create booking payment') && $booking->payment_status != 'paid'): ?>
        <a class="btn btn-warning btn-sm ml-5 customModal" href="#" data-size="md"
            data-url="<?php echo e(route('booking.payment.create', $booking->id)); ?>" data-title="<?php echo e(__('Create Payment')); ?>"> <i
                class="ti-credit-card mr-5"></i>
            <?php echo e(__('Payment')); ?>

        </a>
    <?php endif; ?>
    <?php if(Gate::check('edit booking')): ?>
        <a class="btn btn-primary btn-sm ml-5"
            href="<?php echo e(route('booking.edit', \Illuminate\Support\Facades\Crypt::encrypt($booking->id))); ?>"> <i
                class="ti-pencil mr-5"></i>
            <?php echo e(__('Edit')); ?>

        </a>
    <?php endif; ?>
    <a class="btn btn-secondary print ml-5" href="javascript:void(0);"><i class="fa fa-print"></i> <?php echo e(__('Print')); ?></a>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div id="invoice-print">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-body cdx-invoice">
                        <div id="cdx-invoice">
                            <div class="head-invoice">
                                <div class="codex-brand" style="top: 0; left: 0; max-width: 150px; max-height: 150px;">
                                    <a class="codexbrand-logo" href="Javascript:void(0);">
                                        <img class="img-fluid"
                                            src="<?php echo e(asset(Storage::url('upload/logo/')) . '/' . (isset($settings['company_logo']) && !empty($settings['company_logo']) ? $settings['company_logo'] : 'logo.png')); ?>"
                                            alt="invoice-logo">
                                    </a>
                                    <a class="codexdark-logo" href="Javascript:void(0);">
                                        <img class="img-fluid"
                                            src="<?php echo e(asset(Storage::url('upload/logo/')) . '/' . (isset($settings['company_logo']) && !empty($settings['company_logo']) ? $settings['company_logo'] : 'logo.png')); ?>"
                                            alt="invoice-logo">
                                    </a>
                                </div>
                                <ul class="contact-list">
                                    <li>
                                        <div class="icon-wrap"><i class="fa fa-user"></i></div>
                                        <?php echo e($settings['company_name']); ?>

                                    </li>
                                    <li>
                                        <div class="icon-wrap"><i class="fa fa-phone"></i></div>
                                        <?php echo e($settings['company_phone']); ?>

                                    </li>
                                    <li>
                                        <div class="icon-wrap"><i class="fa fa-envelope"></i></div>
                                        <?php echo e($settings['company_email']); ?>

                                    </li>


                                </ul>
                                <ul class="contact-list">
                                    
                                    <li>
                                        <div class="icon-wrap"><i class="fa fa-info-circle"></i></div>IF:
                                        <?php echo e($settings['if'] ?? ' '); ?>

                                    </li>
                                    <li>
                                        <div class="icon-wrap"><i class="fa fa-info-circle"></i></div>RC:
                                        <?php echo e($settings['rc'] ?? ' '); ?>

                                    </li>
                                    <li>
                                        <div class="icon-wrap"><i class="fa fa-info-circle"></i></div>Patente:
                                        <?php echo e($settings['patente'] ?? ' '); ?>

                                    </li>
                                    <li>
                                        <div class="icon-wrap"><i class="fa fa-info-circle"></i></div>ICE:
                                        <?php echo e($settings['ice'] ?? ' '); ?>

                                    </li>
                                </ul>
                            </div>
                            <div class="invoice-user">
                                <div class="left-user">
                                    <h5><?php echo e(__('Receipt To')); ?>:</h5>
                                    <ul class="detail-list">
                                        <li>
                                            <div class="icon-wrap"><i class="fa fa-user"></i></div>
                                            <?php echo e(!empty($booking->drivers) ? $booking->drivers->name : ''); ?>

                                        </li>
                                        <li>
                                            <div class="icon-wrap"><i class="fa fa-phone"></i></div>
                                            <?php echo e(!empty($booking->drivers) ? $booking->drivers->phone_number : ''); ?>

                                        </li>
                                        <li>
                                            <div class="icon-wrap"><i class="fa fa-envelope"></i></div>
                                            <?php echo e(!empty($booking->drivers) ? $booking->drivers->email : ''); ?>

                                        </li>

                                    </ul>
                                </div>
                                <div class="right-user">
                                    <ul class="detail-list">
                                        <li><?php echo e(__('Booking Date')); ?>: <span> <?php echo e(dateFormat($booking->created_at)); ?></span>
                                        </li>
                                        <li><?php echo e(__('Booking ID')); ?>:
                                            <span><?php echo e(bookingPrefix() . $booking->booking_id); ?></span>
                                        </li>
                                        <li><?php echo e(__('Start Date')); ?>:
                                            <span><?php echo e(dateFormat($booking->start_date)); ?> -
                                                <?php echo e(timeFormat($booking->start_time)); ?></span>
                                        </li>
                                        <li><?php echo e(__('End Date')); ?>:
                                            <span>
                                                <?php echo e(dateFormat($booking->end_date)); ?> -
                                                <?php echo e(timeFormat($booking->end_time)); ?>

                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="body-invoice">
                                <div class="table-responsive1">
                                    <table class="table ml-1">
                                        <thead>
                                            <tr>
                                                <th><?php echo e(__('Vehicle')); ?></th>
                                                <th><?php echo e(!empty($booking->vehicleDetails()) ? $booking->vehicleDetails()->name : '-'); ?>

                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                $details = !empty($booking->details)
                                                    ? json_decode($booking->details)
                                                    : [];

                                            ?>
                                            <tr>
                                                <td><?php echo e(__('Duration')); ?></td>
                                                <td>
                                                    <?php if(isset($details->totalDays) && $details->totalDays > 0): ?>
                                                        <?php echo e($details->totalDays); ?> <?php echo e(__('Days')); ?>

                                                    <?php endif; ?>
                                                    <?php if(isset($details->totalHours) && $details->totalHours > 0): ?>
                                                        ,<?php echo e($details->totalHours); ?> <?php echo e(__('Hours')); ?>

                                                    <?php endif; ?>
                                                    <?php if(isset($details->totalMinuts) && $details->totalMinuts > 0): ?>
                                                        ,<?php echo e($details->totalMinuts); ?> <?php echo e(__('Minuts')); ?>

                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php if(!empty($booking->addon)): ?>
                                                <?php $__currentLoopData = $booking->addons(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $addon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr>
                                                        <td><?php echo e($addon->name); ?></td>
                                                        <td>
                                                            <?php echo e(priceFormat($addon->price)); ?>

                                                        </td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <?php endif; ?>
                                            <tr>
                                                <td><?php echo e(__('Pickup Address')); ?></td>
                                                <td><?php echo e(!empty($booking->pickupAddress) ? $booking->pickupAddress->name : '-'); ?>

                                                    (<?php echo e(priceFormat($booking->pickupAddress->price)); ?>)</td>
                                            </tr>
                                            <tr>
                                                <td><?php echo e(__('Drop Off Address')); ?></td>
                                                <td><?php echo e(!empty($booking->dropOffAddress) ? $booking->dropOffAddress->name : '-'); ?>

                                                    (<?php echo e(priceFormat($booking->dropOffAddress->price)); ?>)</td>
                                            </tr>
                                            
                                            <tr>
                                                <td><?php echo e(__('Payment Status')); ?></td>
                                                <td>
                                                    <?php if($booking->payment_status == 'paye'): ?>
                                                        <span
                                                            class="badge badge-success"><?php echo e(\App\Models\Booking::$paymentStatus[$booking->payment_status]); ?></span>
                                                    <?php elseif($booking->payment_status == 'impaye'): ?>
                                                        <span
                                                            class="badge badge-danger"><?php echo e(\App\Models\Booking::$paymentStatus[$booking->payment_status]); ?></span>
                                                    <?php elseif($booking->payment_status == 'partiellement_paye'): ?>
                                                        <span
                                                            class="badge badge-warning"><?php echo e(\App\Models\Booking::$paymentStatus[$booking->payment_status]); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <div style=" width: 40%; margin-left:auto;">
                            
                               
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr >
                                            <td><?php echo e(__('Total Amount')); ?></td>
                                            <td><?php echo e(priceFormat($booking->getTotalAmount() * 0.8)); ?></td>
                                        </tr>
                                        <tr >
                                            <td>TVA:</td>
                                            <td><?php echo e(priceFormat($booking->getTotalAmount() * 0.2)); ?></td>
                                        </tr>
                                         <tr>
                                            <td><?php echo e(__('Rest')); ?></td>
                                            <td><?php echo e(priceFormat($booking->getTotalDueAmount())); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php echo e(__('Due Amount')); ?></td>
                                            <td><?php echo e(priceFormat($booking->getTotalAmount())); ?></td>
                                        </tr>
                                       
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><?php echo e(__('Payment History')); ?></h5>
                    </div>
                    <div class="card-body">
                        <table class="display dataTable cell-border datatbl-advance1">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('Date')); ?></th>
                                    <th><?php echo e(__('Payment Method')); ?></th>
                                    <th><?php echo e(__('Notes')); ?></th>
                                    <th><?php echo e(__('Amount')); ?></th>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete booking payment')): ?>
                                        <th class="text-right action"><?php echo e(__('Action')); ?></th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $booking->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr role="row">
                                        <td><?php echo e(dateFormat($payment->date)); ?> </td>
                                        <td><?php echo e($payment->payment_method); ?> </td>
                                        <td><?php echo e($payment->notes); ?> </td>
                                        <td><?php echo e(priceFormat($payment->amount)); ?> </td>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete booking payment')): ?>
                                            <td class="text-right action">
                                                <div class="cart-action">
                                                    <?php echo Form::open(['method' => 'DELETE', 'route' => ['booking.payment.destroy', $booking->id, $payment->id]]); ?>

                                                    <a class=" text-danger confirm_dialog" data-bs-toggle="tooltip"
                                                        data-bs-original-title="<?php echo e(__('Detete')); ?>" href="#"> <i
                                                            data-feather="trash-2"></i></a>
                                                    <?php echo Form::close(); ?>

                                                </div>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('script-page'); ?>
    <script>
        $(document).on('click', '.print', function() {
            $('.action').addClass('d-none');
            var printContents = document.getElementById('invoice-print').innerHTML;
            var originalContents = document.body.innerHTML;

            document.body.innerHTML = printContents;

            window.print();

            document.body.innerHTML = originalContents;
            $('.action').removeClass('d-none');
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/directonderweg/resources/views/booking/show.blade.php ENDPATH**/ ?>