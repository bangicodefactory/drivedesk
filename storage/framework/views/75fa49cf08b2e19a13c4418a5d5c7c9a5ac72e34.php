<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Place')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?php echo e(route('dashboard')); ?>"><h1><?php echo e(__('Dashboard')); ?></h1></a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">
                <?php echo e(__('Place')); ?>

            </a>
        </li>
    </ul>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('card-action-btn'); ?>
    <?php if(Gate::check('manage place')): ?>
        <a class="btn btn-primary btn-sm ml-20 customModal" href="#" data-size="md"
           data-url="<?php echo e(route('place.create')); ?>"
           data-title="<?php echo e(__('Create Place')); ?>"> <i
                class="ti-plus mr-5"></i>
            <?php echo e(__('Create Place')); ?>

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
                            <th><?php echo e(__('Name')); ?></th>
                            <th><?php echo e(__('City')); ?></th>
                            <th><?php echo e(__('Island')); ?></th>
                            <th><?php echo e(__('Price')); ?></th>
                            <th><?php echo e(__('Depo name')); ?></th>
                            <th><?php echo e(__('Depo address')); ?></th>
                            <?php if(Gate::check('edit place') || Gate::check('delete place')): ?>
                                <th><?php echo e(__('Action')); ?></th>
                            <?php endif; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $__currentLoopData = $places; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $place): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($place->name); ?> </td>
                                <td><?php echo e($place->city); ?> </td>
                                <td><?php echo e($place->island); ?> </td>
                                <td><?php echo e(priceFormat($place->price)); ?> </td>
                                <td><?php echo e(!empty($place->depo_name)?$place->depo_name:'-'); ?> </td>
                                <td><?php echo e(!empty($place->depo_address)?$place->depo_address:'-'); ?> </td>
                                <?php if(Gate::check('edit place') || Gate::check('delete place')): ?>
                                    <td>
                                        <div class="cart-action">
                                            <?php echo Form::open(['method' => 'DELETE', 'route' => ['place.destroy', $place->id]]); ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit place')): ?>
                                                <a class="text-success customModal" data-bs-toggle="tooltip"
                                                   data-bs-original-title="<?php echo e(__('Edit')); ?>" href="#" data-size="md"
                                                   data-url="<?php echo e(route('place.edit',$place)); ?>"
                                                   data-title="<?php echo e(__('Edit Place')); ?>"> <i data-feather="edit"></i></a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete place')): ?>
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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/carrent/resources/views/place/index.blade.php ENDPATH**/ ?>