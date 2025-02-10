<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('TVA')); ?>

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
                <?php echo e(__('TVA')); ?>

            </a>
        </li>
    </ul>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('card-action-btn'); ?>
    <?php if(Gate::check('manage reminder')): ?>
        <a class="btn btn-primary btn-sm ml-20 customModal" href="#" data-size="lg"
            data-url="<?php echo e(route('tva.create')); ?>" data-title="<?php echo e(__('Create TVA')); ?>"> <i class="ti-plus mr-5"></i>
            <?php echo e(__('Create TVA')); ?>

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
                                <th><?php echo e(__('Period')); ?></th>
                                <th><?php echo e(__('Total amout')); ?></th>
                                <th><?php echo e(__('TVA amout')); ?></th>
                                <th><?php echo e(__('Status')); ?></th>
                                <th><?php echo e(__('Generate date')); ?></th> 
                                <?php if(Gate::check('edit reminder') || Gate::check('delete reminder')): ?>
                                    <th><?php echo e(__('Action')); ?></th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $tvaFiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tvaFile): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e(Carbon\Carbon::create()->month($tvaFile->month)->format('F')); ?> <?php echo e($tvaFile->year); ?></td>
                                    <td><?php echo e(number_format($tvaFile->total_amount, 2)); ?> </td>
                                    <td><?php echo e(number_format($tvaFile->tva_amount, 2)); ?> </td>
                                    <td><?php echo e($tvaFile->status); ?></td>
                                    <td>
                                        <?php echo e($tvaFile->generated_date->format('d/m/Y')); ?>

                                    </td>
                                    <?php if(Gate::check('edit reminder') || Gate::check('delete reminder')): ?>
                                        <td>
                                            <div class="cart-action">
                                                <?php echo Form::open(['method' => 'DELETE', 'route' => ['tva.destroy', $tvaFile->id]]); ?>


                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit reminder')): ?>
                                                    <a class="text-success customModal" data-bs-toggle="tooltip"
                                                        data-bs-original-title="<?php echo e(__('Edit')); ?>" href="#"
                                                        data-size="lg" data-url="<?php echo e(route('tva.edit', $tvaFile->id)); ?>"
                                                        data-title="<?php echo e(__('Edit tva')); ?>"> <i data-feather="edit"></i></a>
                                                <?php endif; ?>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete reminder')): ?>
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


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/directonderweg/resources/views/tva/index.blade.php ENDPATH**/ ?>