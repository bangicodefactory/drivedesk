<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Booking')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?php echo e(route('dashboard')); ?>"><h1><?php echo e(__('Dashboard')); ?></h1></a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">
                <?php echo e(__('Booking')); ?>

            </a>
        </li>
    </ul>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('card-action-btn'); ?>
    <?php if(Gate::check('manage vehicle')): ?>
        <a class="btn btn-primary btn-sm ml-20" href="<?php echo e(route('booking.create')); ?>"> <i
                class="ti-plus mr-5"></i>
            <?php echo e(__('Create Booking')); ?>

        </a>
    <?php endif; ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table class="display dataTable cell-border datatbl-advance">
                        <thead>
                        <tr>
                            <th><?php echo e(__('ID')); ?></th>
                            <th><?php echo e(__('Driver')); ?></th>
                            <th><?php echo e(__('Vehicle')); ?></th>
                            <th><?php echo e(__('Duration')); ?></th>
                            <th><?php echo e(__('Status')); ?></th>
                            <th><?php echo e(__('Payment Status')); ?></th>
                            <?php if(Gate::check('edit booking') || Gate::check('delete booking') || Gate::check('show booking')): ?>
                                <th><?php echo e(__('Action')); ?></th>
                            <?php endif; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                            <tr>
                                <td><?php echo e(bookingPrefix().$booking->booking_id); ?> </td>
                                <td><?php echo e(!empty($booking->drivers)?$booking->drivers->name:'-'); ?> </td>
                                <td><?php echo e(!empty($booking->vehicleDetails())?$booking->vehicleDetails()->name:'-'); ?> </td>
                                <td>
                                    <?php echo e(dateFormat($booking->start_date) .' / '. timeFormat($booking->start_time)); ?> <br>
                                    <?php echo e(dateFormat($booking->end_date) .' / '. timeFormat($booking->end_time)); ?>

                                </td>
                                <td>
                                    <?php if($booking->status=='yet_to_start'): ?>
                                        <span
                                            class="badge badge-primary"><?php echo e(\App\Models\Booking::$status[$booking->status]); ?></span>
                                    <?php elseif($booking->status=='completed' ): ?>
                                        <span
                                            class="badge badge-success"><?php echo e(\App\Models\Booking::$status[$booking->status]); ?></span>
                                    <?php elseif($booking->status=='on_going'): ?>
                                        <span
                                            class="badge badge-warning"><?php echo e(\App\Models\Booking::$status[$booking->status]); ?></span>
                                    <?php elseif($booking->status=='cancelled'): ?>
                                        <span
                                            class="badge badge-danger"><?php echo e(\App\Models\Booking::$status[$booking->status]); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($booking->payment_status=='paye'): ?>
                                        <span
                                            class="badge badge-success"><?php echo e(\App\Models\Booking::$paymentStatus[$booking->payment_status]); ?></span>
                                    <?php elseif($booking->payment_status=='impaye'): ?>
                                        <span
                                            class="badge badge-danger"><?php echo e(\App\Models\Booking::$paymentStatus[$booking->payment_status]); ?></span>
                                    <?php elseif($booking->payment_status=='partiellement_paye'): ?>
                                        <span
                                            class="badge badge-warning"><?php echo e(\App\Models\Booking::$paymentStatus[$booking->payment_status]); ?></span>
                                    <?php endif; ?>
                                </td>
                                <?php if(Gate::check('edit booking') || Gate::check('delete booking') || Gate::check('show booking')): ?>
                                    <td>
                                        <div class="cart-action">
                                            <?php echo Form::open(['method' => 'DELETE', 'route' => ['booking.destroy', $booking->id]]); ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('show booking')): ?>
                                                <a class="text-warning"
                                                   data-bs-toggle="tooltip"
                                                   data-bs-original-title="<?php echo e(__('Details')); ?>"
                                                   href="<?php echo e(route('booking.show',\Illuminate\Support\Facades\Crypt::encrypt($booking->id))); ?>"
                                                > <i data-feather="eye"></i></a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit booking')): ?>
                                                <a class="text-success" data-bs-toggle="tooltip"
                                                   data-bs-original-title="<?php echo e(__('Edit')); ?>"
                                                   href="<?php echo e(route('booking.edit',\Illuminate\Support\Facades\Crypt::encrypt($booking->id))); ?>">
                                                    <i data-feather="edit"></i></a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete booking')): ?>
                                                <a class=" text-danger confirm_dialog" data-bs-toggle="tooltip"
                                                   data-bs-original-title="<?php echo e(__('Detete')); ?>" href="#"> <i
                                                        data-feather="trash-2"></i></a>
                                            <?php endif; ?>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/carrent/resources/views/booking/index.blade.php ENDPATH**/ ?>