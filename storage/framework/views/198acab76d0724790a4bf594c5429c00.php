<?php echo e(Form::open(array('url'=>'place','method'=>'post'))); ?>

<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            <?php echo e(Form::label('name',__('Name'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::text('name',null,array('class'=>'form-control','placeholder'=>__('Enter place name'),'required'=>'required'))); ?>

        </div>
        <div class="form-group col-md-12">
            <?php echo e(Form::label('city',__('City'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::text('city',null,array('class'=>'form-control','placeholder'=>__('Enter city'),'required'=>'required'))); ?>

        </div>
        <div class="form-group col-md-12">
            <?php echo e(Form::label('island',__('Island'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::text('island',null,array('class'=>'form-control','placeholder'=>__('Enter island'),'required'=>'required'))); ?>

        </div>
        <div class="form-group col-md-12">
            <?php echo e(Form::label('price',__('Price'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::number('price',null,array('class'=>'form-control','placeholder'=>__('Enter price'),'required'=>'required'))); ?>

        </div>
        <div class="form-group col-md-12">
            <?php echo e(Form::label('depo_name',__('Depo name'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::text('depo_name',null,array('class'=>'form-control','placeholder'=>__('Enter depo name')))); ?>

        </div>
        <div class="form-group col-md-12">
            <?php echo e(Form::label('depo_address',__('Depo address'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::text('depo_address',null,array('class'=>'form-control','placeholder'=>__('Enter depo address')))); ?>

        </div>
    </div>
</div>
<div class="modal-footer">
    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal"><?php echo e(__('Close')); ?></button>
    <?php echo e(Form::submit(__('Create'),array('class'=>'btn btn-primary ml-10'))); ?>

</div>
<?php echo e(Form::close()); ?>


<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/carrent/resources/views/place/create.blade.php ENDPATH**/ ?>