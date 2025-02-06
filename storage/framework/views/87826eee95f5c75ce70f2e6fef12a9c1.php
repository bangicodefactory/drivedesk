<?php echo e(Form::open(array('url'=>'vehicle','method'=>'post', 'enctype' => "multipart/form-data"))); ?>

<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            <?php echo e(Form::label('name',__('Vehicle Name'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::text('name',null,array('class'=>'form-control','placeholder'=>__('Enter vehicle name'),'required'=>'required'))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('type', __('Type'),['class'=>'form-label'])); ?>

            <?php echo Form::select('type', $types,null,array('class' => 'form-control hidesearch ','required'=>'required')); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('model',__('Model'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::text('model',null,array('class'=>'form-control','placeholder'=>__('Enter model'),'required'=>'required'))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('engine_type',__('Engine Type'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::text('engine_type',null,array('class'=>'form-control','placeholder'=>__('Enter engine type'),'required'=>'required'))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('engine_no',__('Engine Number'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::text('engine_no',null,array('class'=>'form-control','placeholder'=>__('Enter engine number')))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('license_plate',__('License Plate'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::text('license_plate',null,array('class'=>'form-control','placeholder'=>__('Enter license plate'),'required'=>'required'))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('registration_expiry_date',__('Registration Expiry Date'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::date('registration_expiry_date',null,array('class'=>'form-control'))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('daily_rate',__('Daily Rate'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::number('daily_rate',null,array('class'=>'form-control','placeholder'=>__('Enter daily rate'),'required'=>'required'))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('year_of_ﬁrst_immatriculation',__('Year of First Immatriculation'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::number('year_of_ﬁrst_immatriculation',null,array('class'=>'form-control','placeholder'=>__('Enter Year of First Immatriculation')))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('gearbox', __('Gearbox'),['class'=>'form-label'])); ?>

            <select name="gearbox" class="form-control hidesearch " id="gearbox" required>
                <?php $__currentLoopData = $gearbox; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($k); ?>"><?php echo e($val); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('fuel_type', __('Fuel Type'),['class'=>'form-label'])); ?>

            <select name="fuel_type" class="form-control hidesearch " id="fuel_type" required>
                <?php $__currentLoopData = $fuelType; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($k); ?>"><?php echo e($val); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('number_of_seats',__('Number of Seats'),array('class'=>'form-label','required'=>'required'))); ?>

            <?php echo e(Form::number('number_of_seats',null,array('class'=>'form-control','required'=>'required'))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('kilometers',__('Kilometer'),array('class'=>'form-label','required'=>'required'))); ?>

            <?php echo e(Form::number('kilometers',null,array('class'=>'form-control','required'=>'required'))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('option', __('Options'),['class'=>'form-label'])); ?>

            <?php echo Form::select('option[]', $option,null,array('class' => 'form-control hidesearch ','multiple')); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('document',__('Document'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::file('document',array('class'=>'form-control'))); ?>

        </div>
        <div class="form-group col-md-6">
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


<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/directonderweg/resources/views/vehicle/create.blade.php ENDPATH**/ ?>