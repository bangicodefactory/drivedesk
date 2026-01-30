@extends('layouts.app')
@section('page-title')
    {{ __('credit.driver_credits') }} — {{ $driver->name }}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">
                <h1>{{ __('credit.dashboard') }}</h1>
            </a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('credit.index') }}">{{ __('credit.title') }}</a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">{{ $driver->name }}</a>
        </li>
    </ul>
@endsection
@section('card-action-btn')
    <a href="{{ route('credit.index') }}" class="btn btn-secondary btn-sm ml-20">
        <i class="ti-arrow-left mr-5"></i>{{ __('credit.back_to_list') }}
    </a>
@endsection
@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-0">{{ __('credit.driver') }}: <strong>{{ $driver->name }}</strong></h5>
                    @if($driver->phone_number)
                        <small class="text-muted">{{ $driver->phone_number }}</small>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row mb-4">
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('credit.credits_by_status') }}</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2" style="height: 280px;">
                        <canvas id="creditsByStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('credit.credits_by_month') }}</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area" style="height: 280px;">
                        <canvas id="creditsByMonthChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Credits table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table class="display dataTable cell-border datatbl-advance" id="driverCreditsTable">
                        <thead>
                            <tr>
                                <th>{{ __('credit.amount') }}</th>
                                <th>{{ __('credit.status') }}</th>
                                <th>{{ __('credit.date_credit') }}</th>
                                <th>{{ __('credit.creation_date') }}</th>
                                @if (Gate::check('manage driver'))
                                    <th>{{ __('credit.action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($credits as $c)
                                <tr>
                                    <td>{{ number_format($c->amount, 2) }} {{ __('credit.currency') }}</td>
                                    <td>
                                        @if ($c->status === 'payé')
                                            <span class="badge badge-success">{{ __('credit.status_paid') }}</span>
                                        @else
                                            <span class="badge badge-warning">{{ __('credit.status_unpaid') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $c->credit_date ? dateFormat($c->credit_date) : __('credit.none') }}</td>
                                    <td>{{ dateFormat($c->created_at) }}</td>
                                    @if (Gate::check('manage driver'))
                                        <td>
                                            <div class="cart-action">
                                                <a class="text-success customModal" data-bs-toggle="tooltip"
                                                   data-bs-original-title="{{ __('credit.edit') }}" href="#"
                                                   data-url="{{ route('credit.edit', $c) }}"
                                                   data-title="{{ __('credit.edit_credit') }}" data-size="md">
                                                    <i data-feather="edit"></i>
                                                </a>
                                                <a href="#" class="text-danger credit-delete-btn" data-bs-toggle="tooltip"
                                                   data-bs-original-title="{{ __('credit.delete') }}"
                                                   data-url="{{ route('credit.destroy', $c) }}">
                                                    <i data-feather="trash-2"></i>
                                                </a>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            if ($.fn.DataTable.isDataTable('#driverCreditsTable')) {
                $('#driverCreditsTable').DataTable().destroy();
            }
            $('#driverCreditsTable').DataTable({
                pageLength: 25,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "{{ __('credit.all') }}"]],
                searching: true,
                ordering: true,
                order: [[2, 'desc']],
                language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/en-GB.json" },
                columnDefs: [
                    @if (Gate::check('manage driver'))
                    { targets: 4, orderable: false }
                    @endif
                ]
            });

            $(document).on('click', '.credit-delete-btn', function(e) {
                e.preventDefault();
                var url = $(this).data('url');
                Swal.fire({
                    title: "{{ __('credit.confirm_deletion') }}",
                    text: "{{ __('credit.delete_credit_confirm') }}",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: "{{ __('credit.yes_delete_it') }}"
                }).then(function(result) {
                    if (result.isConfirmed) {
                        var form = $('<form>', { method: 'POST', action: url }).append(
                            $('<input>', { type: 'hidden', name: '_method', value: 'DELETE' }),
                            $('<input>', { type: 'hidden', name: '_token', value: $('meta[name="csrf-token"]').attr('content') })
                        );
                        $('body').append(form);
                        form.submit();
                    }
                });
            });
        });

        // Chart 1: Credits by status (doughnut)
        var statusCtx = document.getElementById('creditsByStatusChart');
        if (statusCtx) {
            new Chart(statusCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: @json($chartStatus['labels']),
                    datasets: [{
                        data: @json($chartStatus['amounts']),
                        backgroundColor: ['#1cc88a', '#f6c23e'],
                        hoverBorderColor: 'rgba(234, 236, 244, 1)',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    var total = context.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                                    var pct = total ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                    return context.label + ': ' + context.parsed.toLocaleString() + ' {{ __('credit.currency') }} (' + pct + '%)';
                                }
                            }
                        },
                        legend: { display: true, position: 'bottom' }
                    }
                }
            });
        }

        // Chart 2: Credits by month (bar)
        var monthCtx = document.getElementById('creditsByMonthChart');
        if (monthCtx) {
            new Chart(monthCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: @json($chartByMonth['months']),
                    datasets: [{
                        label: "{{ __('credit.amount') }} ({{ __('credit.currency') }})",
                        data: @json($chartByMonth['amounts']),
                        backgroundColor: 'rgba(78, 115, 223, 0.8)',
                        borderColor: '#4e73df',
                        borderWidth: 1,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString() + ' {{ __('credit.currency') }}';
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y.toLocaleString();
                                }
                            }
                        },
                        legend: { display: false }
                    }
                }
            });
        }
    </script>
@endpush
