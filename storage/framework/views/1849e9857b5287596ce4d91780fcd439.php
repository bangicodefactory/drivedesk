<?php echo e(Form::open(array('url'=>'TVA','method'=>'post', 'enctype' => "multipart/form-data"))); ?>

<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            <?php echo e(Form::label('name',__('Title'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::text('name',null,array('class'=>'form-control','placeholder'=>__('Enter reminder titel'),'required'=>'required'))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('type', __('Reminder Type'),['class'=>'form-label'])); ?>

            <?php echo Form::select('type', $types,null,array('class' => 'form-control hidesearch ')); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('vehicle', __('Vehicle'),['class'=>'form-label'])); ?>

            <?php echo Form::select('vehicle', $vehicles,null,array('class' => 'form-control hidesearch ')); ?>

        </div>

        <div class="form-group col-md-6">
            <?php echo e(Form::label('reminder_date',__('Reminder Date'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::date('reminder_date',null,array('class'=>'form-control','required'=>'required'))); ?>

        </div>
        <div class="form-group col-md-12">
            <?php echo e(Form::label('note',__('Notes'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::textarea('note',null,array('class'=>'form-control','placeholder'=>__('Reminder Description'),'rows'=>2))); ?>

        </div>
    </div>
</div>
<div class="modal-footer">
    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal"><?php echo e(__('Close')); ?></button>
    <?php echo e(Form::submit(__('Create'),array('class'=>'btn btn-primary ml-10'))); ?>

</div>
<?php echo e(Form::close()); ?>


<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/directonderweg/resources/views/tva/create.blade.php ENDPATH**/ ?>