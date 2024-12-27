<?php echo e(Form::open(array('route'=>array('booking.payment.store',$booking->id),'method'=>'post'))); ?>

<div class="modal-body">
    <div class="row">
        <div class="form-group">
            <?php echo e(Form::label('date',__('Date'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::date('date',date('Y-m-d'),array('class'=>'form-control','required'=>'required'))); ?>

        </div>
        <div class="form-group">
            <?php echo e(Form::label('amount',__('Amount'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::number('amount',$booking->getTotalDueAmount(),array('class'=>'form-control','placeholder'=>__('Enter payment amount'),'required'=>'required'))); ?>

        </div>
        <div class="form-group">
            <?php echo e(Form::label('payment_method', __('Method'),['class'=>'form-label'])); ?>

            <?php echo Form::select('payment_method', $paymentMethod,null,array('class' => 'form-control hidesearch ')); ?>

        </div>
        <div class="form-group">
            <?php echo e(Form::label('notes',__('Notes'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::textarea('notes',null,array('class'=>'form-control','placeholder'=>__('Enter notes'),'rows'=>1))); ?>

        </div>
    </div>
</div>
<div class="modal-footer">
    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal"><?php echo e(__('Close')); ?></button>
    <?php echo e(Form::submit(__('Create'),array('class'=>'btn btn-primary ml-10'))); ?>

</div>
<?php echo e(Form::close()); ?>


<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/carrent/resources/views/booking/payment.blade.php ENDPATH**/ ?>