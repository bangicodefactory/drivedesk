<?php echo e(Form::open(['url' => 'reminder', 'method' => 'post', 'enctype' => 'multipart/form-data'])); ?>

<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            <?php echo e(Form::label('name', __('Title'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter reminder titel'), 'required' => 'required'])); ?>

        </div>
        <div class="form-group col-md-6">
            <?php echo e(Form::label('type', __('Reminder Type'), ['class' => 'form-label'])); ?>

            <?php echo Form::select('type', $types, null, ['class' => 'form-control hidesearch ']); ?>

        </div>
        <div class="form-group col-md-6">
            
            <?php echo e(Form::label('vehicle', __('Vehicle'), ['class' => 'form-label'])); ?>

            <select name="vehicle" id="vehicle" class="form-control basic-select" required>
                <option value=""><?php echo e(__('Select Vehicle')); ?></option>
                <?php $__currentLoopData = $vehicles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vehicle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($vehicle->id); ?>">
                        <?php echo e($vehicle->name . ' - ' . $vehicle->license_plate); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <div class="form-group col-md-6">
            <?php echo e(Form::label('reminder_date', __('Reminder Date'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::date('reminder_date', null, ['class' => 'form-control', 'required' => 'required'])); ?>

        </div>
        <div class="form-group col-md-12">
            <?php echo e(Form::label('note', __('Notes'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::textarea('note', null, ['class' => 'form-control', 'placeholder' => __('Reminder Description'), 'rows' => 2])); ?>

        </div>
    </div>
</div>
<div class="modal-footer">
    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal"><?php echo e(__('Close')); ?></button>
    <?php echo e(Form::submit(__('Create'), ['class' => 'btn btn-primary ml-10'])); ?>

</div>
<?php echo e(Form::close()); ?>

<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/directonderweg/resources/views/reminder/create.blade.php ENDPATH**/ ?>