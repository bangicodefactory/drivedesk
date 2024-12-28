<div class="modal-body">
    <div class="product-card">
        <div class="row">
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('ID')); ?></h6>
                    <p class="mb-20"><?php echo e(!empty($driver)?driverPrefix().$driver->driver_id:'-'); ?></p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('First Name')); ?></h6>
                    <p class="mb-20"><?php echo e($user->first_name); ?></p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('Last Name')); ?></h6>
                    <p class="mb-20"><?php echo e($user->last_name); ?></p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('Email')); ?></h6>
                    <p class="mb-20"><?php echo e($user->email); ?></p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('Phone Number')); ?></h6>
                    <p class="mb-20"><?php echo e(!empty($user->phone_number)?$user->phone_number:'-'); ?></p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('Gender')); ?></h6>
                    <p class="mb-20"><?php echo e(!empty($driver)?$driver->gender:'-'); ?></p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('Age')); ?></h6>
                    <p class="mb-20"><?php echo e(!empty($driver) && $driver->age!=0?$driver->age:'-'); ?></p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('Address')); ?></h6>
                    <p class="mb-20"><?php echo e(!empty($driver) && !empty($driver->address)?$driver->address:'-'); ?></p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('Birth Date')); ?></h6>
                    <p class="mb-20"><?php echo e(!empty($driver) && !empty($driver->birth_date)?dateFormat($driver->birth_date):'-'); ?> </p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('License Number')); ?></h6>
                    <p class="mb-20"><?php echo e(!empty($driver) && !empty($driver->license_number)?$driver->license_number:'-'); ?></p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('Issue Date')); ?></h6>
                    <p class="mb-20"><?php echo e(!empty($driver) && !empty($driver->issue_date)?dateFormat($driver->issue_date):'-'); ?></p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('Expiration Date')); ?></h6>
                    <p class="mb-20"><?php echo e(!empty($driver) && !empty($driver->expiration_date)?dateFormat($driver->expiration_date):'-'); ?></p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('Document')); ?></h6>
                    <p class="mb-20">
                        <?php if(!empty($driver) && !empty($driver->document)): ?>
                            <a href="<?php echo e(asset(Storage::url('upload/document'.'/'.$driver->document))); ?>"
                               target="_blank"><?php echo e($driver->document); ?></a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('License')); ?></h6>
                    <p class="mb-20">
                        <?php if(!empty($driver) && !empty($driver->license)): ?>
                            <a href="<?php echo e(asset(Storage::url('upload/license'.'/'.$driver->license))); ?>"
                               target="_blank"> <?php echo e(!empty($driver)?$driver->license:'-'); ?></a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('Reference')); ?></h6>
                    <p class="mb-20"><?php echo e(!empty($driver) && !empty($driver->reference)?$driver->reference:'-'); ?></p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('notes')); ?></h6>
                    <p class="mb-20"><?php echo e(!empty($driver) && !empty($driver->notes)?$driver->notes:'-'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>



<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/carrent/resources/views/driver/show.blade.php ENDPATH**/ ?>