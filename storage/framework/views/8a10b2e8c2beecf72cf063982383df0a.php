<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Expense')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?php echo e(route('dashboard')); ?>"><h1><?php echo e(__('Dashboard')); ?></h1></a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">
                <?php echo e(__('Expense')); ?>

            </a>
        </li>
    </ul>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('card-action-btn'); ?>
    <?php if(Gate::check('manage expense')): ?>
        <a class="btn btn-primary btn-sm ml-20 customModal" href="#" data-size="lg"
           data-url="<?php echo e(route('expense.create')); ?>"
           data-title="<?php echo e(__('Create Expense')); ?>"> <i
                class="ti-plus mr-5"></i>
            <?php echo e(__('Create Expense')); ?>

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
                            <th><?php echo e(__('Date')); ?></th>
                            <th><?php echo e(__('Title')); ?></th>
                            <th><?php echo e(__('Type')); ?></th>
                            <th><?php echo e(__('Vehicle')); ?></th>
                            <th><?php echo e(__('Amount')); ?></th>
                            <th><?php echo e(__('Notes')); ?></th>
                            <?php if(Gate::check('edit expense') || Gate::check('delete expense')): ?>
                                <th><?php echo e(__('Action')); ?></th>
                            <?php endif; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $__currentLoopData = $expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td> <?php echo e(dateFormat($expense->date)); ?> </td>
                                <td><?php echo e($expense->title); ?> </td>
                                <td><?php echo e(!empty($expense->types)?$expense->types->title:'-'); ?> </td>
                                <td><?php echo e(!empty($expense->vehicles)?$expense->vehicles->name:'-'); ?> </td>
                                <td><?php echo e(priceFormat($expense->amount)); ?> </td>
                                <td>
                                    <?php echo e($expense->notes); ?>

                                </td>
                                <?php if(Gate::check('edit expense') || Gate::check('delete expense') ): ?>
                                    <td>
                                        <div class="cart-action">
                                            <?php echo Form::open(['method' => 'DELETE', 'route' => ['expense.destroy', $expense->id]]); ?>

                                            <?php if(!empty($expense->receipt)): ?>
                                                <a  class="text-primary"  href="<?php echo e(asset('/storage/upload/expense/'.$expense->receipt)); ?> "
                                                   target="_blank" data-bs-toggle="tooltip"
                                                    data-bs-original-title="<?php echo e(__('Receipt')); ?>"> <i data-feather="file"></i> </a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit expense')): ?>
                                                <a class="text-success customModal" data-bs-toggle="tooltip"
                                                   data-bs-original-title="<?php echo e(__('Edit')); ?>" href="#" data-size="lg"
                                                   data-url="<?php echo e(route('expense.edit',$expense->id)); ?>"
                                                   data-title="<?php echo e(__('Edit Expense')); ?>"> <i data-feather="edit"></i></a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete expense')): ?>
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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/carrent/resources/views/expense/index.blade.php ENDPATH**/ ?>