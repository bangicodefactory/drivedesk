<style>
    @media print {
        .conditions-generales {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            page-break-inside: avoid;
        }


        .conditions-generales .terms-content {
            width: 100% !important;
            display: block !important;
            column-count: 1 !important;
        }

        .conditions-generales p {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            text-align: left !important;
        }

        .signatures {
            /* page-break-after: always;  */
            /* margin-top: 50px;  */
        }

        * {
            font-size: 26px !important;
            font-weight: bold;
        }

        /* Exclude specific divs by class or ID */

        .terms-content p {
            font-size: 12px !important;

        }

        .signature-image {
            max-width: 130px;
            max-height: 80px;
            border: none;
            display: block;
        }

        .signature-placeholder {
            border-bottom: 1px solid #000;
            width: 150px;
            height: 30px;
        }

        @media print {
            .signature-image {
                max-width: 300px !important;
                max-height: 150px !important;
                width: auto !important;
                height: auto !important;
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .signatures {
                margin-top: 20px !important;
                margin-bottom: 20px !important;
                min-height: 150px !important;
            }

            .signatures .col-md-4 {
                height: 150px !important;
                vertical-align: top !important;
            }

            /* Make sure signatures appear clearly in print */
            /* .signatures img {
        -webkit-print-color-adjust: exact !important;
        color-adjust: exact !important;
        print-color-adjust: exact !important;
    } */
            .signature-placeholder {
                border-bottom: 1px solid #000;
                width: 200px;
                height: 100px;
            }
        }
    }
</style>


<?php $__env->startSection('page-title'); ?>
    <?php echo e(rentalAgreementPrefix() . $rentalAgreement->agreement_id); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?php echo e(route('dashboard')); ?>">
                <h1><?php echo e(__('Dashboard')); ?></h1>
            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="<?php echo e(route('rental-agreement.index')); ?>">
                <?php echo e(__('Rental Agreement')); ?>

            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">
                <?php echo e(rentalAgreementPrefix() . $rentalAgreement->agreement_id); ?>

            </a>
        </li>
    </ul>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('card-action-btn'); ?>
    <a class="btn btn-secondary print ml-5" href="javascript:void(0);"><i class="fa fa-print"></i> <?php echo e(__('Print')); ?></a>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div id="invoice-print">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-body cdx-invoice">
                        <div id="cdx-invoice">
                            <div class="head-invoice">
                                <div class="codex-brand" style="top: 0; left: 0; max-width: 130px; max-height: 130px;">
                                    <a class="codexbrand-logo" href="Javascript:void(0);">
                                        <img class="img-fluid"
                                            src="<?php echo e(asset('storage/upload/logo/' . ($settings['company_logo'] ?? 'logo.png'))); ?>"
                                            alt="invoice-logo">
                                    </a>
                                    
                                </div>
                                <ul class="contact-list">
                                    <li>
                                        <div class="icon-wrap"><i class="fa fa-user"></i></div>
                                        <?php echo e($settings['company_name']); ?>

                                    </li>
                                    <li>
                                        <div class="icon-wrap"><i class="fa fa-phone"></i></div>
                                        <?php echo e($settings['company_phone']); ?>

                                    </li>
                                    <li>
                                        <div class="icon-wrap"><i class="fa fa-envelope"></i></div>
                                        <?php echo e($settings['company_email']); ?>

                                    </li>

                                </ul>
                                <ul class="contact-list">
                                    
                                    <li>
                                        <div class="icon-wrap"><i class="fa fa-info-circle"></i></div>IF:
                                        <?php echo e($settings['if'] ?? ' '); ?>

                                    </li>
                                    <li>
                                        <div class="icon-wrap"><i class="fa fa-info-circle"></i></div>RC:
                                        <?php echo e($settings['rc'] ?? ' '); ?>

                                    </li>
                                    <li>
                                        <div class="icon-wrap"><i class="fa fa-info-circle"></i></div>Patente:
                                        <?php echo e($settings['patente'] ?? ' '); ?>

                                    </li>
                                    <li>
                                        <div class="icon-wrap"><i class="fa fa-info-circle"></i></div>ICE:
                                        <?php echo e($settings['ice'] ?? ' '); ?>

                                    </li>
                                </ul>

                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <h5 class="text-primary mb-10">
                                        <?php echo e(__('Agreement')); ?> : </h5>

                                    <div class="col-md-4 col-lg-4 col-sm-4">
                                        <div class="detail-group">
                                            <h6><?php echo e(__('Agreement ID')); ?></h6>
                                            <p class="mb-20">
                                                <?php echo e(rentalAgreementPrefix() . $rentalAgreement->agreement_id); ?>

                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-lg-4 col-sm-4">
                                        <div class="detail-group">
                                            <h6><?php echo e(__('Agreement Date')); ?></h6>
                                            <p class="mb-20"> <?php echo e(dateFormat($rentalAgreement->date)); ?> </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-lg-4 col-sm-4">
                                        <div class="detail-group">
                                            <h6><?php echo e(__('Rental Start Date')); ?></h6>
                                            <p class="mb-20">
                                                <?php echo e(dateFormatHourMinute($rentalAgreement->rental_start_date)); ?> </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-lg-4 col-sm-4">
                                        <div class="detail-group">
                                            <h6><?php echo e(__('Rental End Date')); ?></h6>
                                            <p class="mb-20"><?php echo e(dateFormatHourMinute($rentalAgreement->rental_end_date)); ?>

                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-lg-4 col-sm-4">
                                        <div class="detail-group">
                                            <h6><?php echo e(__('Rental Duration')); ?></h6>
                                            <p class="mb-20"><?php echo e($rentalAgreement->rental_duration . __(' Days')); ?> </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-lg-4 col-sm-4">
                                        <div class="detail-group">
                                            <h6><?php echo e(__('Status')); ?></h6>
                                            <p class="mb-20">
                                                <?php if($rentalAgreement->status == 'draft'): ?>
                                                    <span
                                                        class="badge badge-info"><?php echo e(\App\Models\RentalAgreement::$status[$rentalAgreement->status]); ?></span>
                                                <?php elseif($rentalAgreement->status == 'pending'): ?>
                                                    <span
                                                        class="badge badge-warning"><?php echo e(\App\Models\RentalAgreement::$status[$rentalAgreement->status]); ?></span>
                                                <?php elseif($rentalAgreement->status == 'confirmed' || $rentalAgreement->status == 'active'): ?>
                                                    <span
                                                        class="badge badge-success"><?php echo e(\App\Models\RentalAgreement::$status[$rentalAgreement->status]); ?></span>
                                                <?php else: ?>
                                                    <span
                                                        class="badge badge-danger"><?php echo e(\App\Models\RentalAgreement::$status[$rentalAgreement->status]); ?></span>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <h5 class="text-primary mb-10">
                                        <?php echo e(__('Driver')); ?> : </h5>
                                    <div class="col-md-4 col-lg-4 col-sm-4">
                                        <div class="detail-group">
                                            <h6><?php echo e(__('Name')); ?></h6>
                                            <p class="mb-20">
                                                <?php echo e(!empty($rentalAgreement->drivers) ? $rentalAgreement->drivers->name : ''); ?>

                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-lg-4 col-sm-4">
                                        <div class="detail-group">
                                            <h6><?php echo e(__('License Number')); ?></h6>
                                            <p class="mb-20">
                                                <?php echo e(!empty($user_1->drivers) ? $user_1->drivers->license_number : ''); ?> </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-lg-4 col-sm-4">
                                        <div class="detail-group">
                                            <h6><?php echo e(__('Phone Number')); ?></h6>
                                            <p class="mb-20">
                                                <?php echo e(!empty($rentalAgreement->drivers) ? $rentalAgreement->drivers->phone_number : ''); ?>

                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-lg-4 col-sm-4">
                                        <div class="detail-group">
                                            <h6><?php echo e(__('Address')); ?></h6>
                                            <p class="mb-20">
                                                <?php echo e(!empty($user_1->drivers) ? $user_1->drivers->address : ''); ?>

                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-lg-4 col-sm-4">
                                        <div class="detail-group">
                                            <h6><?php echo e(__('Birth Date')); ?></h6>
                                            <p class="mb-20">
                                                <?php echo e(!empty($user_1->drivers) ? $user_1->drivers->birth_date : ''); ?></p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-lg-4 col-sm-4">
                                        <div class="detail-group">
                                            <h6>ID National:</h6>
                                            <p class="mb-20">
                                                <?php echo e(!empty($user_1->drivers) ? $user_1->drivers->reference : ''); ?>

                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if($driver_2 && $user_2): ?>
                                    <div class="row">
                                        <h5 class="text-primary mb-10">
                                            <?php echo e(__('Driver2')); ?> : </h5>
                                        <div class="col-md-4 col-lg-4 col-sm-4">
                                            <div class="detail-group">
                                                <h6><?php echo e(__('Name')); ?></h6>
                                                <p class="mb-20"><?php echo e(!empty($user_2->name) ? $user_2->name : ''); ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-lg-4 col-sm-4">
                                            <div class="detail-group">
                                                <h6><?php echo e(__('License Number')); ?></h6>
                                                <p class="mb-20">
                                                    <?php echo e(!empty($driver_2->license_number) ? $driver_2->license_number : ''); ?>

                                                </p>
                                            </div>
                                        </div>

                                        <div class="col-md-4 col-lg-4 col-sm-4">
                                            <div class="detail-group">
                                                <h6><?php echo e(__('Phone Number')); ?></h6>
                                                <p class="mb-20">
                                                    <?php echo e(!empty($user_2->phone_number) ? $user_2->phone_number : ''); ?>

                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-lg-4 col-sm-4">
                                            <div class="detail-group">
                                                <h6><?php echo e(__('Address')); ?></h6>
                                                <p class="mb-20">
                                                    <?php echo e(!empty($driver_2->address) ? $driver_2->address : ' '); ?>

                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-lg-4 col-sm-4">
                                            <div class="detail-group">
                                                <h6><?php echo e(__('Birth Date')); ?></h6>
                                                <p class="mb-20">
                                                    <?php echo e(!empty($driver_2->birth_date) ? $driver_2->birth_date : ' '); ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-lg-4 col-sm-4">
                                            <div class="detail-group">
                                                <h6>ID National:</h6>
                                                <p class="mb-20">
                                                    <?php echo e(!empty($driver_2->reference) ? $driver_2->reference : ' '); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <hr>
                                <div class="row">
                                    <h5 class="text-primary mb-10">
                                        <?php echo e(__('Vehicle')); ?> : </h5>
                                    <div class="col-md-4 col-lg-4 col-sm-4">
                                        <div class="detail-group">
                                            <h6><?php echo e(__('Vehicle')); ?></h6>
                                            <p class="mb-20">
                                                <?php echo e(!empty($rentalAgreement->vehicles) ? $rentalAgreement->vehicles->name : ''); ?>

                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-lg-4 col-sm-4">
                                        <div class="detail-group">
                                            <h6><?php echo e(__('Model')); ?></h6>
                                            <p class="mb-20">
                                                <?php echo e(!empty($rentalAgreement->vehicles) ? $rentalAgreement->vehicles->model : ''); ?>

                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-lg-4 col-sm-4">
                                        <div class="detail-group">
                                            <h6><?php echo e(__('License Plate')); ?></h6>
                                            <p class="mb-20">
                                                <?php echo e(!empty($rentalAgreement->vehicles) ? $rentalAgreement->vehicles->license_plate : ''); ?>

                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row signatures">
                                    <div class="col-md-4 col-lg-4 col-sm-4">
                                        <h5><?php echo e(__('Signature')); ?></h5>
                                    </div>
                                    <div class="col-md-4 col-lg-4 col-sm-4" >
                                        <h5><?php echo e(__('Signature_client1')); ?></h5>
                                        <?php if(isset($driver1Signature) && $driver1Signature): ?>
                                            <img src="<?php echo e($driver1Signature); ?>" class="signature-image" style="max-width: 150px;">
                                        <?php else: ?>
                                            <div class="signature-placeholder"></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4 col-lg-4 col-sm-4" >
                                        <h5><?php echo e(__('Signature_client2')); ?></h5>
                                        <?php if(isset($driver2Signature) && $driver2Signature): ?>
                                            <img src="<?php echo e($driver2Signature); ?>" class="signature-image" style="max-width: 150px;">
                                        <?php else: ?>
                                            <div class="signature-placeholder"></div>
                                        <?php endif; ?>
                                    </div>

                                </div>
                                <hr>
                                <div class="row conditions-generales">
                                    <h5 class="text-primary mb-10">
                                        <?php echo e(__('Terms & Conditions')); ?> : </h5>

                                    <div class="terms-content">
                                        <p>

                                            <?php echo $terms; ?>

                                        </p>

                                    </div>


                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('script-page'); ?>
    <script>
        $(document).on('click', '.print', function() {
            $('.action').addClass('d-none');
            var printContents = document.getElementById('invoice-print').innerHTML;
            var originalContents = document.body.innerHTML;

            document.body.innerHTML = printContents;

            window.print();

            document.body.innerHTML = originalContents;
            $('.action').removeClass('d-none');
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/directonderweg/resources/views/rental_agreement/show.blade.php ENDPATH**/ ?>