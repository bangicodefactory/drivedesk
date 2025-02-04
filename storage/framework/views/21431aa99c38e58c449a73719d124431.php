<?php
    $profile=asset(Storage::url('upload/profile/'));
?>
<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Driver')); ?>


<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?php echo e(route('dashboard')); ?>"><h1><?php echo e(__('Dashboard')); ?></h1></a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">
                <?php echo e(__('Driver')); ?>

            </a>
        </li>
    </ul>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('card-action-btn'); ?>
    <?php if(Gate::check('manage driver')): ?>
        <a class="btn btn-primary btn-sm ml-20 customModal" href="#" data-size="lg"
           data-url="<?php echo e(route('driver.create')); ?>"
           data-title="<?php echo e(__('Create Driver')); ?>"> <i
                class="ti-plus mr-5"></i>
            <?php echo e(__('Create Driver')); ?>

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
                            <th><?php echo e(__('Email')); ?></th>
                            <th><?php echo e(__('Phone Number')); ?></th>
                            <th><?php echo e(__('License Number')); ?></th>
                            <th><?php echo e(__('Issue Date')); ?></th>
                            <th><?php echo e(__('Expiration Date')); ?></th>
                            <?php if(Gate::check('manage driver') || Gate::check('create driver') || Gate::check('show driver')): ?>
                                <th><?php echo e(__('Action')); ?></th>
                            <?php endif; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $__currentLoopData = $drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e(!empty($driver->drivers)?driverPrefix().$driver->drivers->driver_id:'-'); ?> </td>
                                <td class="table-user">
                                    <img
                                        src="<?php echo e(!empty($driver->avatar)?asset(Storage::url('upload/profile')).'/'.$driver->avatar:asset(Storage::url('upload/profile')).'/avatar.png'); ?>"
                                        alt="" class="mr-2 avatar-sm rounded-circle user-avatar">
                                    <a href="#" class="text-body font-weight-semibold"><?php echo e($driver->name); ?></a>
                                </td>
                                <td><?php echo e($driver->email); ?> </td>
                                <td><?php echo e(!empty($driver->phone_number)?$driver->phone_number:'-'); ?> </td>
                                <td><?php echo e(!empty($driver->drivers) && !empty($driver->drivers->license_number)?$driver->drivers->license_number:'-'); ?>  </td>
                                <td><?php echo e(!empty($driver->drivers) && !empty($driver->drivers->issue_date)?dateFormat($driver->drivers->issue_date):'-'); ?>  </td>
                                <td><?php echo e(!empty($driver->drivers) && !empty($driver->drivers->expiration_date)?dateFormat($driver->drivers->expiration_date):'-'); ?>  </td>
                                <?php if(Gate::check('edit driver') || Gate::check('delete driver') || Gate::check('show driver')): ?>
                                    <td>
                                        <div class="cart-action">
                                            <?php echo Form::open(['method' => 'DELETE', 'route' => ['driver.destroy', $driver->id]]); ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('show driver')): ?>
                                                <a class="text-warning customModal" data-size="lg"
                                                   data-bs-toggle="tooltip"
                                                   data-bs-original-title="<?php echo e(__('Show')); ?>" href="#"
                                                   data-url="<?php echo e(route('driver.show',$driver->id)); ?>"
                                                   data-title="<?php echo e(__('Details')); ?>"> <i data-feather="eye"></i></a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit driver')): ?>
                                                <a class="text-success customModal" data-bs-toggle="tooltip"
                                                   data-bs-original-title="<?php echo e(__('Edit')); ?>" href="#" data-size="lg"
                                                   data-url="<?php echo e(route('driver.edit',$driver->id)); ?>"
                                                   data-title="<?php echo e(__('Edit Driver')); ?>"> <i data-feather="edit"></i></a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete driver')): ?>
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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/directonderweg/resources/views/driver/index.blade.php ENDPATH**/ ?>