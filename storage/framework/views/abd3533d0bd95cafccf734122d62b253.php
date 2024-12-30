<?php echo e(Form::model($user, array('route' => array('driver.update', $user->id), 'enctype' => "multipart/form-data", 'method' => 'PUT'))); ?>

<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            <?php echo e(Form::label('first_name',__('First Name'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::text('first_name',null,array('class'=>'form-control','placeholder'=>__('Enter First Name'),'required'=>'required'))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('last_name',__('Last Name'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::text('last_name',null,array('class'=>'form-control','placeholder'=>__('Enter First Name'),'required'=>'required'))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('email',__('Email'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::text('email',null,array('class'=>'form-control','placeholder'=>__('Enter Email'),'required'=>'required'))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('phone_number',__('Phone Number'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::text('phone_number',null,array('class'=>'form-control','placeholder'=>__('Enter Phone Number')))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('gender', __('Gender'),['class'=>'form-label'])); ?>

            <?php echo Form::select('gender', $gender,null,array('class' => 'form-control hidesearch ')); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('Age',__('age'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::number('age',$driver->age,array('class'=>'form-control','placeholder'=>__('Enter age')))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('birth_date',__('Birth date'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::date('birth_date',$driver->birth_date,array('class'=>'form-control'))); ?>

        </div>
        <div class="form-group col-md-12">
            <?php echo e(Form::label('address',__('Address'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::textarea('address',$driver->address,array('class'=>'form-control','placeholder'=>__('Enter address'),'rows'=>1))); ?>

        </div>

        <div class="form-group col-md-6">
            <?php echo e(Form::label('license_number',__('License Number'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::text('license_number',$driver->license_number,array('class'=>'form-control','placeholder'=>__('Enter license number')))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('issue_date',__('Issue Date'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::date('issue_date',$driver->issue_date,array('class'=>'form-control'))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('expiration_date',__('Expiration Date'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::date('expiration_date',$driver->expiration_date,array('class'=>'form-control'))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('document',__('Document'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::file('document',array('class'=>'form-control'))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('license',__('License'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::file('license',array('class'=>'form-control'))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('reference',__('Reference'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::text('reference',$driver->reference,array('class'=>'form-control','placeholder'=>__('Enter reference')))); ?>

        </div>
        <div class="form-group col-md-12">
            <?php echo e(Form::label('notes',__('Notes'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::textarea('notes',$driver->notes,array('class'=>'form-control','placeholder'=>__('Enter notes'),'rows'=>1))); ?>

        </div>
    </div>
</div>
<div class="modal-footer">
    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal"><?php echo e(__('Close')); ?></button>
    <?php echo e(Form::submit(__('Update'),array('class'=>'btn btn-primary ml-10'))); ?>

</div>
<?php echo e(Form::close()); ?>


<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/carrent/resources/views/driver/edit.blade.php ENDPATH**/ ?>