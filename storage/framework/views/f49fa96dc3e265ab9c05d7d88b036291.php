<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Edit TVA')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><h1><?php echo e(__('Dashboard')); ?></h1></a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('tva.index')); ?>"><?php echo e(__('TVA List')); ?></a></li>
        <li class="breadcrumb-item active"><?php echo e(__('Edit TVA')); ?></li>
    </ul>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo e(Form::model($tva, ['route' => ['tva.update', $tva->id], 'method' => 'PUT'])); ?>

    <div class="row">
        
        <div class="form-group col-md-6">
            <?php echo e(Form::label('booking_id', __('Booking'), ['class' => 'form-label'])); ?>

            <input type="text" class="form-control" value="<?php echo e($tva->booking_id ?? 'N/A'); ?>" readonly>
            <input type="hidden" name="booking_id" value="<?php echo e($tva->booking_id); ?>">
        </div>
        <div class="form-group col-md-6 col-lg-6">
            <?php echo e(Form::label('designation', __('Vehicle'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::text('designation', null, ['class' => 'form-control'])); ?>

        </div>

        <div class="form-group col-md-6 col-lg-6">
            <?php echo e(Form::label('facture_number', __('Facture Number'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::text('facture_number', bookingPrefix() . $tva->facture_number, ['class' => 'form-control', 'required' => true, 'readonly' => true])); ?>

        </div>
        
        <div class="form-group col-md-6 col-lg-6">
            <?php echo e(Form::label('facture_date', __('Facture Date'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::date('facture_date', $tva->facture_date ? \Carbon\Carbon::parse($tva->facture_date)->format('Y-m-d') : null, ['class' => 'form-control', 'required' => true])); ?>

        </div>
        
        <div class="form-group col-md-6 col-lg-6">
            <?php echo e(Form::label('quantity', __('Duration'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::number('quantity', null, ['class' => 'form-control', 'step' => '1', 'readonly' => true])); ?>

        </div>

        
        <div class="form-group col-md-6 col-lg-6">
            <?php echo e(Form::label('total_ht', __('Total HT'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::number('total_ht', null, ['class' => 'form-control', 'step' => '0.01', 'readonly' => true])); ?>

        </div>

        
        <div class="form-group col-md-6 col-lg-6">
            <?php echo e(Form::label('tva', __('TVA'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::number('tva', null, ['class' => 'form-control', 'step' => '0.01', 'readonly' => true])); ?>

        </div>

        
        <div class="form-group col-md-6 col-lg-6">
            <?php echo e(Form::label('unit_price_ht', __('Unit Price HT'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::number('unit_price_ht', null, ['class' => 'form-control', 'step' => '0.01', 'readonly' => true])); ?>

        </div>


        
        <div class="form-group col-md-6 col-lg-6">
            <?php echo e(Form::label('montant_ttc', __('Montant TTC'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::number('montant_ttc', null, ['class' => 'form-control', 'step' => '0.01'])); ?>

        </div>
    </div>

    <div class="mt-4 text-end">
        <?php echo e(Form::submit(__('Update TVA'), ['class' => 'btn btn-primary'])); ?>

    </div>
    <?php echo e(Form::close()); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/car_directonderweg/resources/views/tva/edit.blade.php ENDPATH**/ ?>