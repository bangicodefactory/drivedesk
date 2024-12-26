<div class="modal-body">
    <div class="product-card">
        <div class="row">
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('Vehicle ID')); ?></h6>
                    <p class="mb-20"><?php echo e(vehiclePrefix().$vehicle->vehicle_id); ?></p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('Vehicle Type')); ?></h6>
                    <p class="mb-20"><?php echo e(!empty($vehicle->types)?$vehicle->types->type:'-'); ?></p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('Vehicle Name')); ?></h6>
                    <p class="mb-20"><?php echo e($vehicle->name); ?></p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('Vehicle Model')); ?></h6>
                    <p class="mb-20"><?php echo e($vehicle->model); ?></p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('Engine Type')); ?></h6>
                    <p class="mb-20"><?php echo e($vehicle->engine_type); ?></p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('Engine Number')); ?></h6>
                    <p class="mb-20"><?php echo e(!empty($vehicle->engine_no)?$vehicle->engine_no:'-'); ?></p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('License Plate')); ?></h6>
                    <p class="mb-20"><?php echo e($vehicle->license_plate); ?></p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('Registration Expiry Date')); ?></h6>
                    <p class="mb-20"><?php echo e(!empty($vehicle->registration_expiry_date)?dateFormat($vehicle->registration_expiry_date):'-'); ?></p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('Daily Rate')); ?></h6>
                    <p class="mb-20"><?php echo e(priceFormat($vehicle->daily_rate)); ?></p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('Year of First Immatriculation')); ?></h6>
                    <p class="mb-20"><?php echo e($vehicle->year_of_ﬁrst_immatriculation!=0 && $vehicle->year_of_ﬁrst_immatriculation!=0000?$vehicle->year_of_ﬁrst_immatriculation:'-'); ?></p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('Gearbox')); ?></h6>
                    <p class="mb-20"><?php echo e(\App\Models\Vehicle::$gearbox[$vehicle->gearbox]); ?></p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('Fuel Type')); ?></h6>
                    <p class="mb-20"><?php echo e(App\Models\Vehicle::$fuelType[$vehicle->fuel_type]); ?></p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('Number of Seats')); ?></h6>
                    <p class="mb-20"><?php echo e($vehicle->number_of_seats); ?></p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('Kilometer')); ?></h6>
                    <p class="mb-20"><?php echo e($vehicle->kilometers); ?></p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('Options')); ?></h6>
                    <p class="mb-20">
                        <?php if(!empty($vehicle->options())): ?>
                        <?php $__currentLoopData = $vehicle->options(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php echo e($option->name); ?> <br>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('Document')); ?></h6>
                    <p class="mb-20">
                        <?php if(!empty($vehicle) && !empty($vehicle->document)): ?>
                            <a href="<?php echo e(asset(Storage::url('upload/document'.'/'.$vehicle->document))); ?>"
                               target="_blank"><?php echo e($vehicle->document); ?></a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <div class="col-12">
                <div class="detail-group">
                    <h6><?php echo e(__('Notes')); ?></h6>
                    <p class="mb-20"><?php echo e(!empty($vehicle->notes)?$vehicle->notes:'-'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>



<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/carrent/resources/views/vehicle/show.blade.php ENDPATH**/ ?>