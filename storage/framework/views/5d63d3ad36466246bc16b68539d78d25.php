<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Planning')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?php echo e(route('dashboard')); ?>"><h1><?php echo e(__('Dashboard')); ?></h1></a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">
                <?php echo e(__('Planning')); ?>

            </a>
        </li>
    </ul>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('css-page'); ?>

<?php $__env->stopPush(); ?>
<?php $__env->startPush('script-page'); ?>
    <script src="<?php echo e(asset('js/index.global.js')); ?>"></script>
    <script>
        var bookingData=<?php echo json_encode($bookingData); ?>;
        var vehicleData=<?php echo json_encode($vehicleData); ?>;
        document.addEventListener('DOMContentLoaded', function () {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                now: new Date(),
                editable: false,
                aspectRatio: 1.8,
                scrollTime: '00:00',
                headerToolbar: {
                    left: 'today prev,next',
                    center: 'title',
                    right: 'resourceTimelineDay,resourceTimelineWeek,resourceTimelineMonth,resourceTimelineYear'
                },
                initialView: 'resourceTimelineMonth',
                views: {

                },
                navLinks: true,
                resourceAreaWidth: '25%',
                resourceAreaHeaderContent: 'Vehicles',
                resources: vehicleData,
                events: bookingData,
                eventContent: function(arg) {
                    let customEventContent = document.createElement('div');
                    customEventContent.innerHTML = `<div class="fc-event-title">${arg.event.title}</div>`;
                    return { domNodes: [customEventContent] };
                },

            });

            calendar.render();
        });
        document.addEventListener('DOMContentLoaded', function () {
            var todayElement = document.querySelector('.fc-day-today');
            if (todayElement) {
                var container = todayElement.closest('.fc-scroller');
                var scrollLeft = todayElement.offsetLeft - (container.offsetWidth / 2) + (todayElement.offsetWidth / 2);
                container.scrollLeft = scrollLeft;
            }
        });
    </script>
<?php $__env->stopPush(); ?>
<?php $__env->startSection('page-class'); ?>
    codex-calendar
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class=" col-xxl-12">
                            <div id='calendar'></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/carrent/resources/views/booking/planning.blade.php ENDPATH**/ ?>