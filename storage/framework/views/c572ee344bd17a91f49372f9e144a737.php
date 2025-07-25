<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('TVA')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?php echo e(route('dashboard')); ?>">
                <h1><?php echo e(__('Dashboard')); ?></h1>
            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">
                <?php echo e(__('TVA')); ?>

            </a>
        </li>
    </ul>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('card-action-btn'); ?>
    
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row mb-4">
        <div class="col-md-12">
            <form method="GET" action="<?php echo e(route('tva.index')); ?>" id="auto-filter-form"
                class="d-flex flex-wrap gap-3 justify-content-end">
                <input type="hidden" name="per_page" value="<?php echo e(request('per_page', 30)); ?>">
                <div>
                    <label for="filter_day" class="form-label"><?php echo e(__('Day')); ?></label>
                    <input type="date" id="filter_day" name="filter_day" class="form-control"
                        value="<?php echo e(request()->get('filter_day')); ?>">
                </div>
                <div>
                    <label for="filter_month" class="form-label"><?php echo e(__('Month')); ?></label>
                    <select id="filter_month" name="filter_month" class="form-control">
                        <option value=""><?php echo e(__('Select Month')); ?></option>
                        <?php $__currentLoopData = [
            '01' => 'January',
            '02' => 'February',
            '03' => 'March',
            '04' => 'April',
            '05' => 'May',
            '06' => 'June',
            '07' => 'July',
            '08' => 'August',
            '09' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December',
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $num => $month): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($num); ?>"
                                <?php echo e(request()->get('filter_month') == $num ? 'selected' : ''); ?>>
                                <?php echo e($month); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label for="filter_year" class="form-label"><?php echo e(__('Year')); ?></label>
                    <select id="filter_year" name="filter_year" class="form-control">
                        <option value=""><?php echo e(__('Select Year')); ?></option>
                        <?php for($year = now()->year; $year >= 2020; $year--): ?>
                            <option value="<?php echo e($year); ?>"
                                <?php echo e(request()->get('filter_year') == $year ? 'selected' : ''); ?>>
                                <?php echo e($year); ?>

                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <form id="bulk-download-form" method="POST" action="<?php echo e(route('tva.bulk.download')); ?>">
                <?php echo csrf_field(); ?>

                <button type="submit" class="btn btn-success mb-3"><?php echo e(__('Download Selected Invoices')); ?></button>
                <table class="display dataTable cell-border datatbl-advance" id="tvaTable">
                    <thead>
                        <tr>
                            <th hidden>id</th>
                            <th><input type="checkbox" id="select-all" /></th>
                            <th><?php echo e(__('Facture N°')); ?></th>
                            <th><?php echo e(__('Designation')); ?></th>
                            <th><?php echo e(__('Date')); ?></th>
                            <th><?php echo e(__('TTC')); ?></th>
                            <?php if(Gate::check('edit booking') || Gate::check('delete booking') || Gate::check('show booking')): ?>
                                <th><?php echo e(__('Action')); ?></th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $tvas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tva): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr data-date="<?php echo e($tva->created_at->format('Y-m-d')); ?>">
                                <td hidden><?php echo e($tva->created_at); ?></td>
                                <td>
                                    <input type="checkbox" name="invoice_ids[]" value="<?php echo e($tva->id); ?>" />
                                </td>
                                <!-- To avoid the duplication of the prefix -->
                                
                                <td>
                                    <?php if(isset($tva->facture_number)): ?>
                                        <?php echo e($tva->facture_number); ?>

                                    <?php else: ?>
                                        <?php echo e(__('N/A')); ?>

                                    <?php endif; ?>
                                </td>
                                <td><?php echo e(!empty($tva->designation) ? $tva->designation : '-'); ?></td>

                                <td>
                                    <?php echo e(dateFormat($tva->created_at)); ?>

                                </td>
                                <td>
                                    <?php echo e($tva->montant_ttc); ?> Dh
                                </td>
                                <?php if(Gate::check('edit booking') || Gate::check('delete booking') || Gate::check('show booking')): ?>
                                    <td>
                                        <div class="cart-action">
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('show booking')): ?>
                                                <a class="text-warning customModal" data-size="lg" data-bs-toggle="tooltip"
                                                    data-bs-original-title="<?php echo e(__('Details')); ?>" href="#"
                                                    data-url="<?php echo e(route('tva.show', $tva->id)); ?>"
                                                    data-title="<?php echo e(__('TVA Details')); ?>">
                                                    <i data-feather="eye"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit booking')): ?>
                                                <a class="text-success" data-bs-toggle="tooltip"
                                                    data-bs-original-title="<?php echo e(__('Edit')); ?>"
                                                    href="<?php echo e(route('tva.edit', $tva->id)); ?>">
                                                    <i data-feather="edit"></i></a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete booking')): ?>
                                                <a href="#" class="text-danger delete-btn" data-bs-toggle="tooltip"
                                                    data-bs-original-title="<?php echo e(__('Delete')); ?>"
                                                    data-url="<?php echo e(route('tva.destroy', $tva->id)); ?>">
                                                    <i data-feather="trash-2"></i>
                                                </a>
                                            <?php endif; ?>

                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
                <div class="d-flex justify-content-end mt-3">
                    <?php echo e($tvas->links()); ?>

                </div>
            </form>
        </div>
    </div>
    </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        $(document).ready(function() {
            // Destroy if already initialized
            if ($.fn.DataTable.isDataTable('#tvaTable')) {
                $('#tvaTable').DataTable().destroy();
            }

            var table = $('#tvaTable').DataTable({
                pageLength: 30,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                searching: true,
                ordering: true,
                order: [[0, 'desc']],
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/en-GB.json"
                },
                dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" + // Buttons + search
                    "<'row'<'col-sm-12'tr>>" + // Table
                    "<'row'<'col-sm-6'l><'col-sm-6'p>>" + // Bottom: Length + Pagination
                    "<'row'<'col-sm-12'i>>",
                buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
                columnDefs: [{
                        targets: 0,
                        orderable: false,
                        className: 'select-checkbox'
                    },
                    {
                        targets: '_all',
                        className: 'dt-center'
                    }
                ]
            });

            // === Select All logic (current page only) ===
            $('#select-all').on('click', function(e) {
                e.stopPropagation();
                var isChecked = $(this).prop('checked');
                table.rows({
                    page: 'current'
                }).nodes().to$().find('input[type="checkbox"]').prop('checked', isChecked);
            });

            // === Individual checkbox handler ===
            $('#tvaTable tbody').on('change', 'input[type="checkbox"]', function() {
                updateSelectAllState();
            });

            function updateSelectAllState() {
                var checkboxes = table.rows({
                    page: 'current'
                }).nodes().to$().find('input[type="checkbox"]');
                var checked = checkboxes.filter(':checked').length;
                var total = checkboxes.length;
                $('#select-all').prop('checked', total > 0 && total === checked);
            }

            // === Prevent header checkbox column click from sorting ===
            $('#tvaTable thead').on('click', 'th:first-child', function(e) {
                e.stopPropagation();
            });

            // === Bulk Download ===
            $('#bulk-download-form').on('submit', function(e) {
                e.preventDefault();
                var selectedIds = [];

                table.$('input[type="checkbox"]:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length === 0) {
                    Swal.fire('Error', 'Please select at least one invoice', 'warning');
                    return;
                }

                var form = $('<form>', {
                    method: 'POST',
                    action: $(this).attr('action')
                }).append(
                    $('<input>', {
                        type: 'hidden',
                        name: '_token',
                        value: $('meta[name="csrf-token"]').attr('content')
                    })
                );

                selectedIds.forEach(id => {
                    form.append($('<input>', {
                        type: 'hidden',
                        name: 'invoice_ids[]',
                        value: id
                    }));
                });

                $('body').append(form);
                form.submit();
            });

            function formatLocalDate(date) {
                const yyyy = date.getFullYear();
                const mm = String(date.getMonth() + 1).padStart(2, '0'); // getMonth is zero-based
                const dd = String(date.getDate()).padStart(2, '0');
                return `${yyyy}-${mm}-${dd}`;
            }

        function filterTable() {
            const day = document.getElementById('filter_day').value;
            const month = document.getElementById('filter_month').value;
            const year = document.getElementById('filter_year').value;

            const rows = document.querySelectorAll('#bookingTable tbody tr');

            rows.forEach(row => {
                const date = row.getAttribute('data-date');
                if (!date) return;

                const rowYear = date.substring(0, 4);
                const rowMonth = date.substring(5, 7);

                let show = true;
                if (day && day !== date) show = false;
                if (month && month !== rowMonth) show = false;
                if (year && year !== rowYear) show = false;

                row.style.display = show ? '' : 'none';
            });
        }
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.delete-btn').forEach(element => {
                element.addEventListener('click', function(e) {
                    e.preventDefault();

                    const url = this.getAttribute('data-url');

                    Swal.fire({
                        title: 'Are you sure you want to delete this record ?',
                        text: "This record can not be restore after delete. Do you want to confirm?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = url;

                            const methodInput = document.createElement('input');
                            methodInput.type = 'hidden';
                            methodInput.name = '_method';
                            methodInput.value = 'DELETE';
                            form.appendChild(methodInput);

                            // Add CSRF token
                            const csrfInput = document.createElement('input');
                            csrfInput.type = 'hidden';
                            csrfInput.name = '_token';
                            csrfInput.value = document.querySelector(
                                'meta[name="csrf-token"]').content;
                            form.appendChild(csrfInput);

                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
    
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/car_directonderweg/resources/views/tva/index.blade.php ENDPATH**/ ?>