<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Reminder')); ?>

<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>
<?php echo e(Form::model($reminder, array('route' => array('reminder.update', $reminder->id), 'enctype' => "multipart/form-data", 'method' => 'PUT'))); ?>

<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            <?php echo e(Form::label('name',__('Title'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::text('name',null,array('class'=>'form-control','placeholder'=>__('Enter reminder title'),'required'=>'required'))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('type', __('Reminder Type'),['class'=>'form-label'])); ?>

            <?php echo Form::select('type', $type,null,array('class' => 'form-control hidesearch ')); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('vehicle', __('Vehicle'),['class'=>'form-label'])); ?>

            <?php echo e(Form::text('vehicle_display', $vehicleName, ['class' => 'form-control', 'readonly' => 'readonly'])); ?>

            <?php echo e(Form::hidden('id_vehicle', $reminder->id_vehicle)); ?>

        </div>

        <div class="form-group col-md-6">
            <?php echo e(Form::label('reminder_date',__('Reminder Date'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::date('reminder_date',null,array('class'=>'form-control','required'=>'required'))); ?>

        </div>
        <div class="form-group col-md-12">
            <?php echo e(Form::label('note',__('Notes'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::textarea('note',null,array('class'=>'form-control','placeholder'=>__('Reminder Description'),'rows'=>2))); ?>

        </div>
         <input name="status" id="status" hidden value="<?php echo e($reminder->status); ?>">
         
    </div>
</div>
<div class="modal-footer">
    <button class="btn btn-secondary" type="button" onclick="window.history.back()"><?php echo e(__(key: 'Close')); ?></button>
    <?php echo e(Form::submit(__('Update'),array('class'=>'btn btn-primary ml-10'))); ?>

</div>
<?php echo e(Form::close()); ?>



<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/directonderweg/resources/views/reminder/edit.blade.php ENDPATH**/ ?>