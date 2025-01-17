<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Email Notification Template')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?php echo e(route('dashboard')); ?>">
                <h1><?php echo e(__('Dashboard')); ?></h1>
            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">
                <?php echo e(__('Email Notification Template')); ?>

            </a>
        </li>
    </ul>
<?php $__env->stopSection(); ?>


<?php $__env->startPush('script-page'); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('script-page'); ?>
    <script src="<?php echo e(asset('assets/js/vendors/ckeditor/ckeditor.js')); ?>"></script>
    <script>
        setTimeout(() => {
            feather.replace();
        }, 500);
    </script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table class="display dataTable cell-border datatbl-advance">
                        <thead>
                            <tr>
                                <th><?php echo e(__('Module')); ?></th>
                                <th><?php echo e(__('Subject')); ?></th>
                                <th><?php echo e(__('Email Enable')); ?></th>
                                <?php if(Gate::check('edit notification') || Gate::check('delete notification')): ?>
                                    <th><?php echo e(__('Action')); ?></th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($item->name); ?> </td>
                                    <td><?php echo e($item->subject); ?></td>
                                    <td>

                                        <?php if($item->enabled_email == 1): ?>
                                            <span class="d-inline badge badge-primary"><?php echo e(__('Enable')); ?></span>
                                        <?php else: ?>
                                            <span class="d-inline badge badge-danger"><?php echo e(__('Disable')); ?></span>
                                        <?php endif; ?>

                                    </td>
                                    <?php if(Gate::check('edit notification') || Gate::check('delete notification')): ?>
                                        <td>
                                            <div class="cart-action">
                                                <?php echo Form::open(['method' => 'DELETE', 'route' => ['notification.destroy', $item->id]]); ?>

                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit notification')): ?>
                                                    <a class="text-success customModal" data-bs-toggle="tooltip" data-size="lg"
                                                        data-bs-original-title="<?php echo e(__('Edit')); ?>" href="#"
                                                        data-url="<?php echo e(route('notification.edit', $item->id)); ?>"
                                                        data-title="<?php echo e(__('Edit Notification')); ?>"> <i
                                                            data-feather="edit"></i></a>
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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/directonderweg/resources/views/notification/index.blade.php ENDPATH**/ ?>