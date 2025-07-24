@extends('layouts.app')
@section('page-title')
    {{ __('TVA') }}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">
                <h1>{{ __('Dashboard') }}</h1>
            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">
                {{ __('TVA') }}
            </a>
        </li>
    </ul>
@endsection
@section('card-action-btn')
    @if (Gate::check('manage reminder'))
        <a class="btn btn-primary btn-sm ml-20 customModal" href="#" data-size="lg" data-url="{{ route('tva.create') }}"
            data-title="{{ __('Create TVA') }}"> <i class="ti-plus mr-5"></i>
            {{ __('Create TVA') }}
        </a>
    @endif
@endsection

@section('content')
    <div class="row mb-4">
        <div class="col-md-12">
            <form method="GET" action="{{ route('tva.index') }}" id="auto-filter-form">
                <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
                    <div class="d-flex flex-wrap gap-3">
                        <div class="mb-4">
                            <label for="from_date" class="form-label">{{ __('From Date') }}</label>
                            <input type="date" id="from_date" name="from_date" class="form-control"
                                value="{{ request()->get('from_date') }}">
                        </div>
                        <div class="mb-4">
                            <label for="to_date" class="form-label">{{ __('To Date') }}</label>
                            <input type="date" id="to_date" name="to_date" class="form-control"
                                value="{{ request()->get('to_date') }}">
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-3 justify-content-end">
                        <div>
                            <label for="filter_day" class="form-label">{{ __('Day') }}</label>
                            <input type="date" id="filter_day" name="filter_day" class="form-control"
                                value="{{ request()->get('filter_day') }}">
                        </div>
                        <div>
                            <label for="filter_month" class="form-label">{{ __('Month') }}</label>
                            <select id="filter_month" name="filter_month" class="form-control">
                                <option value="">{{ __('Select Month') }}</option>
                                @foreach ([
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
        ] as $num => $month)
                                    <option value="{{ $num }}"
                                        {{ request()->get('filter_month') == $num ? 'selected' : '' }}>
                                        {{ $month }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="filter_year" class="form-label">{{ __('Year') }}</label>
                            <select id="filter_year" name="filter_year" class="form-control">
                                <option value="">{{ __('Select Year') }}</option>
                                @for ($year = now()->year; $year >= 2020; $year--)
                                    <option value="{{ $year }}"
                                        {{ request()->get('filter_year') == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <form id="bulk-download-form" method="POST" action="{{ route('tva.bulk.download') }}">
                @csrf
                <button type="submit" class="btn btn-success mb-3">{{ __('Download Selected Invoices') }}</button>
                <table class="display dataTable cell-border datatbl-advance" id="bookingTable">
                    <thead>
                        <tr>
                            <th hidden>id</th>
                            <th><input type="checkbox" id="select-all" /></th>
                            <th>{{ __('Facture N°') }}</th>
                            <th>{{ __('Designation') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('TTC') }}</th>
                            @if (Gate::check('edit booking') || Gate::check('delete booking') || Gate::check('show booking'))
                                <th>{{ __('Action') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tvas as $tva)
                            <tr data-date="{{ $tva->created_at->format('Y-m-d') }}">
                                <td>
                                    <input type="checkbox" name="invoice_ids[]" value="{{ $tva->id }}" />
                                </td>
                                <td hidden>{{ $tva->id }}</td>
                                <!-- To avoid the duplication of the prefix -->
                                {{-- @php
                                    $prefix = bookingPrefix();
                                    $factureNumber = Str::startsWith($tva->facture_number, $prefix) ? $tva->facture_number : $prefix . $tva->facture_number;
                                    @endphp
                                    <td>{{ $factureNumber }}</td> --}}
                                <td>
                                    @if (isset($tva->facture_number))
                                        {{ $tva->facture_number }}
                                    @else
                                        {{ __('N/A') }}
                                    @endif
                                </td>
                                <td>{{ !empty($tva->designation) ? $tva->designation : '-' }}</td>

                                <td>
                                    {{ dateFormat($tva->created_at) }}
                                </td>
                                <td>
                                    {{ $tva->montant_ttc }} Dh
                                </td>
                                @if (Gate::check('edit booking') || Gate::check('delete booking') || Gate::check('show booking'))
                                    <td>
                                        <div class="cart-action">
                                            @can('show booking')
                                                <a class="text-warning customModal" data-size="lg" data-bs-toggle="tooltip"
                                                    data-bs-original-title="{{ __('Details') }}" href="#"
                                                    data-url="{{ route('tva.show', $tva->id) }}"
                                                    data-title="{{ __('TVA Details') }}">
                                                    <i data-feather="eye"></i>
                                                </a>
                                            @endcan
                                            @can('edit booking')
                                                <a class="text-success" data-bs-toggle="tooltip"
                                                    data-bs-original-title="{{ __('Edit') }}"
                                                    href="{{ route('tva.edit', $tva->id) }}">
                                                    <i data-feather="edit"></i></a>
                                            @endcan
                                            @can('delete booking')
                                                <a href="#" class="text-danger delete-btn" data-bs-toggle="tooltip"
                                                    data-bs-original-title="{{ __('Delete') }}"
                                                    data-url="{{ route('tva.destroy', $tva->id) }}">
                                                    <i data-feather="trash-2"></i>
                                                </a>
                                            @endcan

                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </form>
        </div>
    </div>
    </div>
    </div>
@endsection
{{-- @push('scripts')
    <script>
        $(document).ready(function() {
            $('.reminder-date').on('click', function(e) {
                e.preventDefault();

                var reminderDate = $(this).data('date');
                var today = new Date();
                var reminder = new Date(reminderDate);
                var diffTime = reminder.getTime() - today.getTime();
                var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                var message = '';
                if (diffDays > 0) {
                    message = '{{ __('There are') }} ' + diffDays +
                        ' {{ __('days remaining until this reminder.') }}';
                } else if (diffDays < 0) {
                    message = '{{ __('This reminder is overdue by') }} ' + Math.abs(diffDays) +
                        ' {{ __('days.') }}';
                } else {
                    message = '{{ __('This reminder is due today!') }}';
                }

                $('#daysRemaining').html(message);
                $('#dateModal').modal('show');
            });
        });
    </script>
@endpush --}}
@push('scripts')
    <script>
        $(document).ready(function() {
            // Destroy if already initialized
            if ($.fn.DataTable.isDataTable('#bookingTable')) {
                $('#bookingTable').DataTable().destroy();
            }

            var table = $('#bookingTable').DataTable({
                pageLength: 30,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                searching: true,
                ordering: true,
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
            $('#bookingTable tbody').on('change', 'input[type="checkbox"]', function() {
                updateSelectAllState();
            });

            // === Update header checkbox when page changes ===
            table.on('draw', function() {
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
            $('#bookingTable thead').on('click', 'th:first-child', function(e) {
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
                const day = $('#filter_day').val();
                const month = $('#filter_month').val();
                const year = $('#filter_year').val();
                const fromDate = $('#from_date').val();
                const toDate = $('#to_date').val();

                $.fn.dataTable.ext.search.push(function(settings, data) {
                    const rawDate = data[4]; // adjust index if needed
                    const parsedDate = new Date(rawDate);

                    if (isNaN(parsedDate)) return false;

                    const rowDateStr = formatLocalDate(parsedDate);
                    const rowYear = rowDateStr.substring(0, 4);
                    const rowMonth = rowDateStr.substring(5, 7);

                    if (day && day !== rowDateStr) return false;
                    if (month && month !== rowMonth) return false;
                    if (year && year !== rowYear) return false;
                    if (fromDate && rowDateStr < fromDate) return false;
                    if (toDate && rowDateStr > toDate) return false;

                    return true;
                });

                table.draw();
                $.fn.dataTable.ext.search.pop();
            }


            $('#filter_day, #filter_month, #filter_year, #from_date, #to_date').on('change', filterTable);

            // === Delete Button ===
            $(document).on('click', '.delete-btn', function(e) {
                e.preventDefault();
                var url = $(this).data('url');

                Swal.fire({
                    title: 'Confirm Deletion',
                    text: "This action cannot be undone. Are you sure?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        var form = $('<form>', {
                            method: 'POST',
                            action: url
                        }).append(
                            $('<input>', {
                                type: 'hidden',
                                name: '_method',
                                value: 'DELETE'
                            }),
                            $('<input>', {
                                type: 'hidden',
                                name: '_token',
                                value: $('meta[name="csrf-token"]').attr('content')
                            })
                        );
                        $('body').append(form);
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
