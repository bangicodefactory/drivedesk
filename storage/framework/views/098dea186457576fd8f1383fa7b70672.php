<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Rental Agreement')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?php echo e(route('dashboard')); ?>"><h1><?php echo e(__('Dashboard')); ?></h1></a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">
                <?php echo e(__('Rental Agreement')); ?>

            </a>
        </li>
    </ul>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('card-action-btn'); ?>
    <?php if(Gate::check('manage rental agreement')): ?>
        <a class="btn btn-primary btn-sm ml-20 customModal" href="#" data-size="lg"
           data-url="<?php echo e(route('rental-agreement.create')); ?>"
           data-title="<?php echo e(__('Create Rental Agreement')); ?>"> <i
                class="ti-plus mr-5"></i>
            <?php echo e(__('Create Agreement')); ?>

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
                            <th><?php echo e(__('Date')); ?></th>
                            <th><?php echo e(__('Rental Start Date')); ?></th>
                            <th><?php echo e(__('Rental End Date')); ?></th>
                            <th><?php echo e(__('Rental Duration')); ?></th>
                            <th><?php echo e(__('Status')); ?></th>
                            <?php if(Gate::check('edit rental agreement') || Gate::check('delete rental agreement') || Gate::check('show rental agreement')): ?>
                                <th><?php echo e(__('Action')); ?></th>
                            <?php endif; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $__currentLoopData = $agreements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agreement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e(rentalAgreementPrefix().$agreement->agreement_id); ?> </td>
                                <td><?php echo e(!empty($agreement->drivers)?$agreement->drivers->name:'-'); ?></td>
                                <td><?php echo e(!empty($agreement->vehicles)?$agreement->vehicles->name.' - '.$agreement->vehicles->license_plate:'-'); ?></td>
                                <td><?php echo e(dateFormat($agreement->date)); ?> </td>
                                <td><?php echo e(dateFormat($agreement->rental_start_date)); ?> </td>
                                <td><?php echo e(dateFormat($agreement->rental_end_date)); ?> </td>
                                <td><?php echo e($agreement->rental_duration.' '.__('Days')); ?> </td>
                                <td>
                                    <?php if($agreement->status=='draft'): ?>
                                        <span class="badge badge-info"><?php echo e(\App\Models\RentalAgreement::$status[$agreement->status]); ?></span>
                                    <?php elseif($agreement->status=='pending'): ?>
                                        <span class="badge badge-warning"><?php echo e(\App\Models\RentalAgreement::$status[$agreement->status]); ?></span>
                                    <?php elseif($agreement->status=='confirmed' || $agreement->status=='active'): ?>
                                        <span class="badge badge-success"><?php echo e(\App\Models\RentalAgreement::$status[$agreement->status]); ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-danger"><?php echo e(\App\Models\RentalAgreement::$status[$agreement->status]); ?></span>
                                    <?php endif; ?>
                                </td>
                                <?php if(Gate::check('edit rental agreement') || Gate::check('delete rental agreement') || Gate::check('show rental agreement')): ?>
                                    <td>
                                        <div class="cart-action">
                                            <?php echo Form::open(['method' => 'DELETE', 'route' => ['rental-agreement.destroy', $agreement->id]]); ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('show rental agreement')): ?>
                                                <a class="text-warning"
                                                   data-bs-toggle="tooltip"
                                                   data-bs-original-title="<?php echo e(__('Details')); ?>" href="<?php echo e(route('rental-agreement.show',\Illuminate\Support\Facades\Crypt::encrypt($agreement->id))); ?>"
                                                  > <i data-feather="eye"></i></a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit rental agreement')): ?>
                                                <a class="text-success customModal" data-bs-toggle="tooltip"
                                                   data-bs-original-title="<?php echo e(__('Edit')); ?>" href="#" data-size="lg"
                                                   data-url="<?php echo e(route('rental-agreement.edit',$agreement->id)); ?>"
                                                   data-title="<?php echo e(__('Edit Rental Agreement')); ?>"> <i data-feather="edit"></i></a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete rental agreement')): ?>
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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/carrent/resources/views/rental_agreement/index.blade.php ENDPATH**/ ?>