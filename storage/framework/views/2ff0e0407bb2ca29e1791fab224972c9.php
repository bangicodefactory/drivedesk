<?php echo e(Form::open(array('url'=>'driver','method'=>'post', 'enctype' => "multipart/form-data"))); ?>

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

            <?php echo e(Form::text('email',null,array('class'=>'form-control','placeholder'=>__('Enter Email')))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('phone_number',__('Phone Number'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::text('phone_number',null,array('class'=>'form-control','placeholder'=>__('Enter Phone Number'),'required'=>'required'))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('gender', __('Gender'),['class'=>'form-label'])); ?>

            <?php echo Form::select('gender', $gender,null,array('class' => 'form-control hidesearch ','required'=>'required')); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('Age',__('age'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::number('age',null,array('class'=>'form-control','placeholder'=>__('Enter age')))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('birth_date',__('Birth date'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::date('birth_date',null,array('class'=>'form-control','required'=>'required'))); ?>

        </div>
        <div class="form-group col-md-12">
            <?php echo e(Form::label('address',__('Address'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::textarea('address',null,array('class'=>'form-control','placeholder'=>__('Enter address'),'required'=>'required','rows'=>1))); ?>

        </div>

        <div class="form-group col-md-12">
            <?php echo e(Form::label('license_number',__('License Number'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::text('license_number',null,array('class'=>'form-control','placeholder'=>__('Enter license number'),'required'=>'required'))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('issue_date',__('Issue Date'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::date('issue_date',null,array('class'=>'form-control','required'=>'required'))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('expiration_date',__('Expiration Date'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::date('expiration_date',null,array('class'=>'form-control','required'=>'required'))); ?>

        </div>
        
        <div class="form-group col-md-6">
            <?php echo e(Form::label('license',__('License 1:'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::file('license',array('class'=>'form-control'))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('license1',__('License 2:'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::file('license1',array('class'=>'form-control'))); ?>

        </div>
        <div class="form-group col-md-12">
            <?php echo e(Form::label('reference',__('Reference'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::text('reference',null,array('class'=>'form-control','placeholder'=>__('Enter reference')))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('document',__('ID file 1:'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::file('document',array('class'=>'form-control'))); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('document1',__('ID file 2:'),array('class'=>'form-label'))); ?>

            <?php echo e(Form::file('document1',array('class'=>'form-control'))); ?>

        </div>
        <div class="form-group col-md-12">
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


<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/directonderweg/resources/views/driver/create.blade.php ENDPATH**/ ?>