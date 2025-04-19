<?php
    $profile = asset(Storage::url('upload/profile/'));
?>
<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Driver')); ?>

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
                <?php echo e(__('Signature')); ?>

            </a>
        </li>
    </ul>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('card-action-btn'); ?>
    <?php if(Gate::check('manage driver')): ?>
        <a class="btn btn-primary btn-sm ml-20" href="<?php echo e(route('signature.create')); ?>"> <i class="ti-plus mr-5"></i>
            <?php echo e(__('Create Signature')); ?>

        </a>
    <?php endif; ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table class="display dataTable cell-border datatbl-advance" id="signatureTable">
                        <thead>
                            <tr>
                                <th hidden>ID</th>
                                <th><?php echo e(__('Client')); ?></th>
                                <th><?php echo e(__('Signature')); ?></th>
                                <th><?php echo e(__('Created At')); ?></th>
                                <th><?php echo e(__('Action')); ?></th>
                                
                                
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $signatures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $signature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td hidden><?php echo e($signature->id); ?></td>
                                    <td><?php echo e($signature->user->name); ?></td>
                                    <td>
                                        <img src="<?php echo e(Storage::url($signature->signature_path)); ?>" alt="Signature"
                                            style="max-height: 100px;">
                                    </td>
                                    <td><?php echo e($signature->created_at->format('Y-m-d H:i')); ?></td>
                                    <td>
                                        <div class="cart-action">

                                            <?php if(Storage::disk('public')->exists($signature->signature_path)): ?>
                                                <a href="<?php echo e(asset('storage/' . $signature->signature_path)); ?>"
                                                    class="btn btn-sm btn-info" target="_blank">View Full Size</a>
                                            <?php endif; ?>
                                            <?php if(Gate::check('delete driver')): ?>
                                                <?php echo Form::open(['method' => 'DELETE', 'route' => ['signature.destroy', $signature->id]]); ?>

                                                <a class=" text-danger confirm_dialog" data-bs-toggle="tooltip"
                                                    data-bs-original-title="<?php echo e(__('Detete')); ?>" href="#"> <i
                                                        data-feather="trash-2"></i></a>
                                                <?php echo Form::close(); ?>

                                            <?php endif; ?>
                                            

                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No signatures found</td>
                                </tr>
                            <?php endif; ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
<script>
$(document).ready(function() {
    // Destroy existing DataTable if it exists
    if ($.fn.DataTable.isDataTable('#signatureTable')) {
        $('#signatureTable').DataTable().destroy();
    }
    
    // Reinitialize
    $('#signatureTable').DataTable({
        columnDefs: [
            // Your column definitions
        ],
        order: [[0, 'desc']]
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/directonderweg/resources/views/signature/index.blade.php ENDPATH**/ ?>