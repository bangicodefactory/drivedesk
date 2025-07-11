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
                    <table class="display dataTable cell-border datatbl-advance" id="bookingTable">
                        <thead>
                        <tr>
                            <th hidden>id</th>
                            <th><?php echo e(__('Facture N°')); ?></th>
                            <th><?php echo e(__('Designation')); ?></th>
                            <th><?php echo e(__('Date')); ?></th>
                            <th><?php echo e(__('TTC')); ?></th>
                            <?php if(Gate::check('edit booking') || Gate::check('delete booking') || Gate::check('show booking')): ?>
                                <th><?php echo e(__('Action')); ?></th>
                            <?php endif; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $__currentLoopData = $tvas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tva): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td hidden><?php echo e($tva->id); ?></td>
                                <td><?php echo e(bookingPrefix().$tva->facture_number); ?></td>
                                <td><?php echo e(!empty($tva->designation)?$tva->designation:'-'); ?></td>

                                <td>
                                    <?php echo e(dateFormat($tva->created_at)); ?>

                                </td>
                                <td>
                                    <?php echo e($tva->montant_ttc); ?> Dh
                                </td>
                                
                                
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