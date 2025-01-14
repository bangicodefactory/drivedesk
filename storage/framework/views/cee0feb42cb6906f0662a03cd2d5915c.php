<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Reminder')); ?>

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
                <?php echo e(__('Reminder')); ?>

            </a>
        </li>
    </ul>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('card-action-btn'); ?>
    <?php if(Gate::check('manage reminder')): ?>
        <a class="btn btn-primary btn-sm ml-20 customModal" href="#" data-size="lg"
            data-url="<?php echo e(route('reminder.create')); ?>" data-title="<?php echo e(__('Create Reminder')); ?>"> <i class="ti-plus mr-5"></i>
            <?php echo e(__('Create Reminder')); ?>

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
                                <th><?php echo e(__('Next appointment date')); ?></th>
                                <th><?php echo e(__('Title')); ?></th>
                                <th><?php echo e(__('Type')); ?></th>
                                <th><?php echo e(__('Vehicle')); ?></th>
                                <th><?php echo e(__('Status')); ?></th>
                                <th><?php echo e(__('Notes')); ?></th>
                                <?php if(Gate::check('edit reminder') || Gate::check('delete reminder')): ?>
                                    <th><?php echo e(__('Action')); ?></th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $reminders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reminder): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <a href="#" 
                                        class="customModal" 
                                        data-bs-toggle="tooltip"
                                        data-bs-original-title="<?php echo e(__('View days remaining')); ?>"
                                        data-size="sm"
                                        data-url="<?php echo e(route('reminder.days-remaining', $reminder->id)); ?>"
                                        data-title="<?php echo e(__('Jours avant le rappel')); ?>">
                                         <?php echo e(dateFormat($reminder->reminder_date)); ?>

                                     </a>
                                    </td>
                                    <td><?php echo e($reminder->name); ?> </td>
                                    <td><?php echo e(!empty($reminder->reminderType) ? $reminder->reminderType->type : '-'); ?> </td>
                                    <td><?php echo e(!empty($reminder->vehicles) ? $reminder->vehicles->name : '-'); ?> </td>
                                    <td>
                                        
                                        <?php if($reminder->status == 'pending'): ?>
                                            <span class="badge bg-primary text-white"><?php echo e(__('Pending')); ?></span>
                                        <?php elseif($reminder->status == 'upcoming'): ?>
                                            <span class="badge bg-secondary text-white"><?php echo e(__('Upcoming')); ?></span>
                                        <?php elseif($reminder->status == 'urgent'): ?>
                                            <span class="badge bg-warning text-dark"><?php echo e(__('Urgent')); ?></span>
                                        <?php elseif($reminder->status == 'overdue'): ?>
                                            <span class="badge bg-danger text-white"><?php echo e(__('Overdue')); ?></span>
                                        <?php endif; ?>

                                    </td>
                                    <td>
                                        <?php echo e($reminder->note); ?>

                                    </td>
                                    <?php if(Gate::check('edit reminder') || Gate::check('delete reminder')): ?>
                                        <td>
                                            <div class="cart-action">
                                                <?php echo Form::open(['method' => 'DELETE', 'route' => ['reminder.destroy', $reminder->id]]); ?>


                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit reminder')): ?>
                                                    <a class="text-success customModal" data-bs-toggle="tooltip"
                                                        data-bs-original-title="<?php echo e(__('Edit')); ?>" href="#"
                                                        data-size="lg" data-url="<?php echo e(route('reminder.edit', $reminder->id)); ?>"
                                                        data-title="<?php echo e(__('Edit reminder')); ?>"> <i data-feather="edit"></i></a>
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


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/directonderweg/resources/views/reminder/index.blade.php ENDPATH**/ ?>