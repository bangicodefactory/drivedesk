<?php echo e(Form::open(['url' => 'addon', 'method' => 'post'])); ?>

<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            <?php echo e(Form::label('name', __('Addon'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter addon name'), 'required' => 'required'])); ?>

        </div>
        <div class="form-group col-md-12">
            <?php echo e(Form::label('price', __('Price'), ['class' => 'form-label'])); ?>

            <?php echo e(Form::number('price', null, ['class' => 'form-control', 'placeholder' => __('Enter price'), 'required' => 'required'])); ?>

        </div>
        <div class="form-group col-md-12">
            <?php echo e(Form::label('billing_type', __('Billing Type'), ['class' => 'form-label'])); ?>

            <select name="billing_type" class="form-control hidesearch " id="billing_type">
                <?php $__currentLoopData = $billingType; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($k); ?>"><?php echo e($val); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal"><?php echo e(__('Close')); ?></button>
    <?php echo e(Form::submit(__('Create'), ['class' => 'btn btn-primary ml-10'])); ?>

</div>
<?php echo e(Form::close()); ?>

<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/carrent/resources/views/addon/create.blade.php ENDPATH**/ ?>