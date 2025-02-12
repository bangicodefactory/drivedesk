<style>
    .card-tools {
        position: absolute;
        right: 1rem;
        top: 1rem;
    }
    
    .list-group-item:hover {
        background-color: #f8f9fa;
    }
    </style>

<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Dashboard')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?php echo e(route('dashboard')); ?>"><h1><?php echo e(__('Dashboard')); ?></h1></a>
        </li>
    </ul>
<?php $__env->stopSection(); ?>
<?php
    $settings=settings();
?>
<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-xxl-3 col-sm-6 cdx-xxl-50">
            <div class="card sale-revenue">
                <div class="card-header">
                    <h4><?php echo e(__('Total Driver')); ?></h4>
                </div>
                <div class="card-body progressCounter">
                    <h2>
                        <span class="">
                            <?php echo e($result['totalDriver']); ?>

                        </span>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-sm-6 cdx-xxl-50">
            <div class="card sale-revenue">
                <div class="card-header">
                    <h4><?php echo e(__('Total Booking')); ?></h4>
                </div>
                <div class="card-body progressCounter">
                    <h2>
                        <span class=""><?php echo e($result['totalBooking']); ?></span>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-sm-6 cdx-xxl-50">
            <div class="card sale-revenue">
                <div class="card-header">
                    <h4><?php echo e(__('Total Income')); ?></h4>
                </div>
                <div class="card-body progressCounter">
                    <h2>
                        <span class=""><?php echo e(priceFormat($result['totalIncome'])); ?></span>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-sm-6 cdx-xxl-50">
            <div class="card sale-revenue">
                <div class="card-header">
                    <h4><?php echo e(__('Total Expense')); ?></h4>
                </div>
                <div class="card-body progressCounter">
                    <h2>
                        <span class=""><?php echo e(priceFormat($result['totalExpense'])); ?></span>
                    </h2>
                </div>
            </div>
        </div>

<div class="col-12">
    <div class="card shadow-sm">
        <div class="card-header  py-3" style="background-color: #197CBC;">
            <div class="text-center mb-2">
                <h5 class="card-title mb-0 fw-bolder " style="color: #f8f9fa;">Notifications</h5>
            </div>
            <div class="text-end">
                <span class="badge badge-light" ><?php echo e(count($reminders)); ?> New</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                <?php $__empty_1 = true; $__currentLoopData = $reminders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reminder): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="list-group-item p-4 border-bottom">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <h6 class="mb-1  text-dark"><?php echo e($reminder->vehicles->name ?? 'N/A'); ?></h6>
                                <p class="mb-0 text-muted">
                                    <i class="fas fa-hashtag me-2"></i><?php echo e($reminder->vehicles->license_plate ?? 'N/A'); ?>

                                </p>
                            </div>
                            <div class="col-md-5">
                                <p class="mb-1 text-muted"><?php echo e($reminder->note ?? 'No description'); ?></p>
                                <p class="mb-0">
                                    <i class="far fa-calendar me-2"></i>
                                    <?php echo e(\Carbon\Carbon::parse($reminder->reminder_date)->format('M d, Y')); ?>

                                </p>
                            </div>
                            <div class="col-md-3 text-md-end">
                                <span class="badge bg-<?php echo e($reminder->status === 'urgent' ? 'danger' : 'warning'); ?> px-3 py-2">
                                    <?php echo e(ucfirst($reminder->status)); ?>

                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="list-group-item p-4 text-center">No notifications found</div>
                <?php endif; ?>
            </div>
        </div>
        <?php if(count($reminders) > 0): ?>
                <div class="card-footer text-center py-3">
                    <a href="<?php echo e(route('reminder.index')); ?>" class="text-primary text-decoration-none">View All</a>
                </div>
            <?php endif; ?>
    </div>
</div>

        <div class="col-xxl-12 cdx-xxl-50">
            <div class="card overall-revenuetbl">
                <div class="card-header">
                    <h4><?php echo e(__('Income Vs Expense')); ?></h4>
                </div>
                <div class="card-body">
                    <div id="incomeExpense"></div>
                </div>
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('script-page'); ?>
    <script>
        var options = {

            series: [{
                name: "<?php echo e(__('Income')); ?>",
                type: 'column',
                data: <?php echo json_encode($result['incomeExpenseByMonth']['income']); ?>,
            }, {
                name: " <?php echo e(__('Expense')); ?>",
                type: 'area',
                data: <?php echo json_encode($result['incomeExpenseByMonth']['expense']); ?>,
            }],
            chart: {
                height: 452,
                type: 'line',
                toolbar:{
                    show: false
                },
                zoom: {
                    enabled: false
                }
            },
            legend:{
                show:false
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                width: [0,0],
                curve: 'smooth',
            },
            plotOptions: {
                bar: {
                    columnWidth:"20%",
                    startingShape:"rounded",
                    endingShape: "rounded",
                }
            },
            fill:{
                opacity:[1, 0.08],
                gradient:{
                    type:"horizontal",
                    opacityFrom:0.5,
                    opacityTo:0.1,
                    stops: [100, 100, 100]
                }
            },
            colors: [Codexdmeki.themeprimary,Codexdmeki.themesecondary],
            states: {
                normal: {
                    filter: {
                        type: 'darken',
                        value: 1,
                    }
                },
                hover: {
                    filter: {
                        type: 'darken',
                        value: 1,
                    }
                },
                active: {
                    allowMultipleDataPointsSelection: false,
                    filter: {
                        type: 'darken',
                        value: 1,
                    }
                },
            },
            grid:{
                strokeDashArray: 2,
            },

            yaxis:{
                tickAmount: 10 ,
                labels:{
                    formatter: function (y) {
                        return  "<?php echo e($result['settings']['CURRENCY_SYMBOL']); ?>" + y.toFixed(0);
                    },
                    style: {
                        colors: '#262626',
                        fontSize: '14px',
                        fontWeight: 500,
                        fontFamily: 'Roboto, sans-serif'
                    }
                },
            },
            xaxis: {
                categories: <?php echo json_encode($result['incomeExpenseByMonth']['label']); ?> ,
                axisTicks: {
                    show:false
                },
                axisBorder:{
                    show:false
                },
                labels:{
                    style: {
                        colors: '#262626',
                        fontSize: '14px',
                        fontWeight: 500,
                        fontFamily: 'Roboto, sans-serif'
                    },
                },
            },
            responsive:[
                {
                    breakpoint: 1441,
                    options:{
                        chart:{
                            height: 445
                        }
                    },
                },
                {
                    breakpoint: 1366,
                    options:{
                        chart:{
                            height: 320
                        }
                    },
                },
            ]
        };
        var chart = new ApexCharts(document.querySelector("#incomeExpense"), options);
        chart.render();
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/directonderweg/resources/views/dashboard/index.blade.php ENDPATH**/ ?>