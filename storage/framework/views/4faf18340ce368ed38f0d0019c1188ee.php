<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Inspection')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?php echo e(route('dashboard')); ?>"><h1><?php echo e(__('Dashboard')); ?></h1></a>
        </li>
        <li class="breadcrumb-item">
            <a href="<?php echo e(route('inspection.index')); ?>">
                <?php echo e(__('Inspection')); ?>

            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">
                <?php echo e(__('Create')); ?>

            </a>
        </li>
    </ul>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('card-action-btn'); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <?php echo e(Form::open(array('url'=>'inspection','method'=>'post','enctype' => "multipart/form-data"))); ?>

    <div class="row">
        <div class="col-md-6 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4><?php echo e(__('Inspection Details')); ?></h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-6 col-lg-6">
                            <?php echo e(Form::label('vehicle', __('Vehicle'),['class'=>'form-label'])); ?>

                            <?php echo Form::select('vehicle', $vehicles,null,array('class' => 'form-control hidesearch ','required'=>'required')); ?>

                        </div>
                        <div class="form-group col-md-6 col-lg-6">
                            <?php echo e(Form::label('inspector ', __('Inspection By'),['class'=>'form-label'])); ?>

                            <?php echo e(Form::text('inspector',null,array('class'=>'form-control','required'=>'required'))); ?>

                        </div>
                        <div class="form-group col-md-6 col-lg-6">
                            <?php echo e(Form::label('inspection_date',__('Inspection Date'),array('class'=>'form-label'))); ?>

                            <?php echo e(Form::date('inspection_date',null,array('class'=>'form-control','required'=>'required'))); ?>

                        </div>
                        <div class="form-group col-md-6 col-lg-6">
                            <?php echo e(Form::label('status', __('Inspection Status'),['class'=>'form-label'])); ?>

                            <?php echo Form::select('status', $status,null,array('class' => 'form-control hidesearch ','required'=>'required')); ?>

                        </div>
                        <div class="form-group col-md-6 col-lg-6">
                            <?php echo e(Form::label('repair_status', __('Repair Status'),['class'=>'form-label'])); ?>

                            <?php echo Form::select('repair_status', $repairStatus,null,array('class' => 'form-control hidesearch ','required'=>'required')); ?>

                        </div>
                        <div class="form-group col-md-6 col-lg-6">
                            <?php echo e(Form::label('notes',__('Notes'),array('class'=>'form-label'))); ?>

                            <?php echo e(Form::textarea('notes',null,array('class'=>'form-control','placeholder'=>__('Enter notes'),'rows'=>2,'required'=>'required'))); ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-6">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-6 col-lg-6">
                                    <?php echo e(Form::label('amount', __('Amount'),['class'=>'form-label'])); ?>

                                    <?php echo e(Form::number('amount',null,array('class'=>'form-control','placeholder'=>__('Enter amount'),'required'=>'required'))); ?>

                                </div>
                                <div class="form-group col-md-6 col-lg-6">
                                    <?php echo e(Form::label('receipt',__('Receipt'),array('class'=>'form-label'))); ?>

                                    <?php echo e(Form::file('receipt',array('class'=>'form-control'))); ?>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4><?php echo e(__('Incoming Details')); ?></h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-6 col-lg-6">
                                    <?php echo e(Form::label('incoming_date',__('Date'),array('class'=>'form-label'))); ?>

                                    <?php echo e(Form::date('incoming_date',null,array('class'=>'form-control'))); ?>

                                </div>

                                <div class="form-group col-md-6 col-lg-6">
                                    <?php echo e(Form::label('meter_reading_incoming',__('Meter Reading (km)'),array('class'=>'form-label'))); ?>

                                    <?php echo e(Form::number('meter_reading_incoming',null,array('class'=>'form-control','placeholder'=>__('Enter meter reading incoming (km)')))); ?>

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4><?php echo e(__('Inspections Checklist')); ?></h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-md-6 col-lg-6">
                                <h6 class="form-label"><?php echo e($type->type); ?></h6>
                                <div class="col-md-12 col-lg-12">
                                    <div class="row">
                                        <div class="form-group col-auto">
                                            <label class="switch with-icon switch-primary">
                                                <input type="checkbox" name="types[<?php echo e($type->id); ?>][type]"><span
                                                    class="switch-btn"></span>
                                            </label>
                                        </div>
                                        <div class="form-group col">
                                            <input class="form-control" type="text" placeholder="<?php echo e(__('Enter notes')); ?>"
                                                   name="types[<?php echo e($type->id); ?>][note]" value="" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row text-end">
        <div class="col-md-12 col-lg-12">
            <?php echo e(Form::submit(__('Create'),array('class'=>'btn btn-primary ml-10'))); ?>

        </div>
    </div>
    <?php echo e(Form::close()); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/carrent/resources/views/inspection/create.blade.php ENDPATH**/ ?>