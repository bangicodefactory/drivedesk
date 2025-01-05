<?php echo e(Form::open(array('url'=>'expense','method'=>'post', 'enctype' => "multipart/form-data"))); ?>

<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            <?php echo e(Form::label('title',__('Title'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::text('title',null,array('class'=>'form-control','placeholder'=>__('Enter Expense title'),'required'=>'required'))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('type', __('Type'),['class'=>'form-label'])); ?>

            <?php echo Form::select('type', $types,null,array('class' => 'form-control hidesearch ')); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('vehicle', __('Vehicle'),['class'=>'form-label'])); ?>

            <?php echo Form::select('vehicle', $vehicles,null,array('class' => 'form-control hidesearch ')); ?>

        </div>

        <div class="form-group col-md-6">
            <?php echo e(Form::label('date',__('Date'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::date('date',null,array('class'=>'form-control','required'=>'required'))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('amount',__('Total Amount'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::number('amount',null,array('class'=>'form-control','placeholder'=>__('Enter expense amount'),'required'=>'required'))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('receipt',__('Receipt'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::file('receipt',array('class'=>'form-control'))); ?>

        </div>
        <div class="form-group col-md-12">
            <?php echo e(Form::label('notes',__('Notes'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::textarea('notes',null,array('class'=>'form-control','placeholder'=>__('Enter notes'),'rows'=>2))); ?>

        </div>
    </div>
</div>
<div class="modal-footer">
    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal"><?php echo e(__('Close')); ?></button>
    <?php echo e(Form::submit(__('Create'),array('class'=>'btn btn-primary ml-10'))); ?>

</div>
<?php echo e(Form::close()); ?>


<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/carrent/resources/views/expense/create.blade.php ENDPATH**/ ?>