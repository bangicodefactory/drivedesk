<div class="modal-body">
    <div class="product-card">
        <div class="row">
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('Facture Number')); ?></h6>
                    <p class="mb-20"><?php echo e(bookingPrefix() . $tva->facture_number); ?></p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('Facture Date')); ?></h6>
                    <p class="mb-20"><?php echo e(\Carbon\Carbon::parse($tva->facture_date)->format('Y-m-d')); ?></p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('Client Name')); ?></h6>
                    <p class="mb-20"><?php echo e($tva->client_name ?? '-'); ?></p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('Duration (Quantity)')); ?></h6>
                    <p class="mb-20"><?php echo e($tva->quantity); ?></p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('Unit Price HT')); ?></h6>
                    <p class="mb-20"><?php echo e(number_format($tva->unit_price_ht, 2)); ?></p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('Total HT')); ?></h6>
                    <p class="mb-20"><?php echo e(number_format($tva->total_ht, 2)); ?></p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('TVA (Tax)')); ?></h6>
                    <p class="mb-20"><?php echo e(number_format($tva->tva, 2)); ?></p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6><?php echo e(__('Montant TTC')); ?></h6>
                    <p class="mb-20"><?php echo e(number_format($tva->montant_ttc, 2)); ?></p>
                </div>
            </div>
            <div class="col-12">
                <div class="detail-group">
                    <h6><?php echo e(__('Vehicle')); ?></h6>
                    <p class="mb-20"><?php echo e($tva->designation ?? '-'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/car_directonderweg/resources/views/tva/show.blade.php ENDPATH**/ ?>