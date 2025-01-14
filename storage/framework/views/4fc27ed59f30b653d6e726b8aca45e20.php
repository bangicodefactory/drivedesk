<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Vehicle')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?php echo e(route('dashboard')); ?>"><h1><?php echo e(__('Dashboard')); ?></h1></a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">
                <?php echo e(__('Vehicle')); ?>

            </a>
        </li>
    </ul>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('card-action-btn'); ?>
    <?php if(Gate::check('manage vehicle')): ?>
        <a class="btn btn-primary btn-sm ml-20 customModal" href="#" data-size="lg"
           data-url="<?php echo e(route('vehicle.create')); ?>"
           data-title="<?php echo e(__('Create Vehicle')); ?>"> <i
                class="ti-plus mr-5"></i>
            <?php echo e(__('Create Vehicle')); ?>

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
                            <th><?php echo e(__('Name')); ?></th>
                            <th><?php echo e(__('Type')); ?></th>
                            <th><?php echo e(__('Model')); ?></th>
                            <th><?php echo e(__('License Plate')); ?></th>
                            <th><?php echo e(__('Registration Expiration Date')); ?></th>
                            <th><?php echo e(__('Engine Type')); ?></th>
                            <?php if(Gate::check('edit vehicle') || Gate::check('delete vehicle') || Gate::check('show vehicle')): ?>
                                <th><?php echo e(__('Action')); ?></th>
                            <?php endif; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $__currentLoopData = $vehicles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vehicle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e(vehiclePrefix().$vehicle->vehicle_id); ?> </td>
                                <td><?php echo e($vehicle->name); ?> </td>
                                <td><?php echo e(!empty($vehicle->types)?$vehicle->types->type:'-'); ?> </td>
                                <td><?php echo e($vehicle->model); ?> </td>
                                <td><?php echo e($vehicle->license_plate); ?> </td>
                                <td><?php echo e(!empty($vehicle->registration_expiry_date)?dateFormat($vehicle->registration_expiry_date):'-'); ?> </td>
                                <td><?php echo e($vehicle->engine_type); ?> </td>
                                <?php if(Gate::check('edit vehicle') || Gate::check('delete vehicle') || Gate::check('show vehicle')): ?>
                                    <td>
                                        <div class="cart-action">
                                            <?php echo Form::open(['method' => 'DELETE', 'route' => ['vehicle.destroy', $vehicle->id]]); ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('show vehicle')): ?>
                                                <a class="text-warning customModal" data-size="lg"
                                                   data-bs-toggle="tooltip"
                                                   data-bs-original-title="<?php echo e(__('Details')); ?>" href="#"
                                                   data-url="<?php echo e(route('vehicle.show',$vehicle->id)); ?>"
                                                   data-title="<?php echo e(__('Vehicle Details')); ?>"> <i data-feather="eye"></i></a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit vehicle')): ?>
                                                <a class="text-success customModal" data-bs-toggle="tooltip"
                                                   data-bs-original-title="<?php echo e(__('Edit')); ?>" href="#" data-size="lg"
                                                   data-url="<?php echo e(route('vehicle.edit',$vehicle->id)); ?>"
                                                   data-title="<?php echo e(__('Edit Vehicle')); ?>"> <i data-feather="edit"></i></a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete vehicle')): ?>
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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/directonderweg/resources/views/vehicle/index.blade.php ENDPATH**/ ?>