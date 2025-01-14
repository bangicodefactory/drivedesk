<?php echo e(Form::open(array('url'=>'reminder-type','method'=>'post'))); ?>

<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            <?php echo e(Form::label('title',__('Title'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::text('type',null,array('class'=>'form-control','placeholder'=>__('Enter title'),'required'=>'required'))); ?>

        </div>
    </div>
</div>
<div class="modal-footer">
    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal"><?php echo e(__('Close')); ?></button>
    <?php echo e(Form::submit(__('Create'),array('class'=>'btn btn-primary ml-10'))); ?>

</div>
<?php echo e(Form::close()); ?>


<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/directonderweg/resources/views/reminder_type/create.blade.php ENDPATH**/ ?>