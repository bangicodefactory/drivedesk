<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Inspection')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?php echo e(route('dashboard')); ?>"><h1><?php echo e(__('Dashboard')); ?></h1></a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">
                <?php echo e(__('Inspection')); ?>

            </a>
        </li>
    </ul>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('card-action-btn'); ?>
    <?php if(Gate::check('manage vehicle')): ?>
        <a class="btn btn-primary btn-sm ml-20" href="<?php echo e(route('inspection.create')); ?>"> <i
                class="ti-plus mr-5"></i>
            <?php echo e(__('Create Inspection')); ?>

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
                            <th><?php echo e(__('Vehicle')); ?></th>
                            <th><?php echo e(__('Inspection Date')); ?></th>
                            <th><?php echo e(__('Inspection By')); ?></th>
                            <th><?php echo e(__('Inspection Status')); ?></th>
                            <th><?php echo e(__('Repair Status')); ?></th>
                            <?php if(Gate::check('edit inspection') || Gate::check('delete inspection') || Gate::check('show inspection')): ?>
                                <th><?php echo e(__('Action')); ?></th>
                            <?php endif; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $__currentLoopData = $inspections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inspection): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e(!empty($inspection->vehicles)?$inspection->vehicles->name:'-'); ?> </td>
                                <td><?php echo e(dateFormat($inspection->inspection_date)); ?> </td>
                                <td><?php echo e($inspection->inspector); ?> </td>
                                <td>
                                    <?php if($inspection->status=='pending' || $inspection->status=='on_hold'): ?>
                                        <span class="badge badge-warning"><?php echo e(\App\Models\Inspection::$status[$inspection->status]); ?></span>
                                    <?php elseif($inspection->status=='completed' || $inspection->status=='conditional_pass'): ?>
                                        <span class="badge badge-success"><?php echo e(\App\Models\Inspection::$status[$inspection->status]); ?></span>
                                    <?php elseif($inspection->status=='in_progress'): ?>
                                        <span class="badge badge-primary"><?php echo e(\App\Models\Inspection::$status[$inspection->status]); ?></span>
                                    <?php elseif($inspection->status=='reject'): ?>
                                        <span class="badge badge-danger"><?php echo e(\App\Models\Inspection::$status[$inspection->status]); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($inspection->repair_status=='pending' || $inspection->repair_status=='on_hold'): ?>
                                        <span class="badge badge-warning"><?php echo e(\App\Models\Inspection::$repairStatus[$inspection->repair_status]); ?></span>
                                    <?php elseif($inspection->repair_status=='completed'): ?>
                                        <span class="badge badge-success"><?php echo e(\App\Models\Inspection::$repairStatus[$inspection->repair_status]); ?></span>
                                    <?php elseif($inspection->repair_status=='in_progress'): ?>
                                        <span class="badge badge-primary"><?php echo e(\App\Models\Inspection::$repairStatus[$inspection->repair_status]); ?></span>
                                    <?php elseif($inspection->repair_status=='needs_repair'): ?>
                                        <span class="badge badge-danger"><?php echo e(\App\Models\Inspection::$repairStatus[$inspection->repair_status]); ?></span>
                                    <?php endif; ?>

                                </td>
                                <?php if(Gate::check('edit inspection') || Gate::check('delete inspection') || Gate::check('show inspection')): ?>
                                    <td>
                                        <div class="cart-action">
                                            <?php echo Form::open(['method' => 'DELETE', 'route' => ['inspection.destroy', $inspection->id]]); ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('show inspection')): ?>
                                                <a class="text-warning"
                                                   data-bs-toggle="tooltip"
                                                   data-bs-original-title="<?php echo e(__('Details')); ?>" href="<?php echo e(route('inspection.show',\Illuminate\Support\Facades\Crypt::encrypt($inspection->id))); ?>"
                                                  > <i data-feather="eye"></i></a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit inspection')): ?>
                                                <a class="text-success" data-bs-toggle="tooltip"
                                                   data-bs-original-title="<?php echo e(__('Edit')); ?>" href="<?php echo e(route('inspection.edit',\Illuminate\Support\Facades\Crypt::encrypt($inspection->id))); ?>"> <i data-feather="edit"></i></a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete inspection')): ?>
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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/directonderweg/resources/views/inspection/index.blade.php ENDPATH**/ ?>