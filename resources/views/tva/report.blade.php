@extends('layouts.app')
@section('page-title')
    {{ __('TVA Report') }}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">
                <h1>{{ __('Dashboard') }}</h1>
            </a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('tva.index') }}">
                {{ __('TVA') }}
            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">
                {{ __('Report') }}
            </a>
        </li>
    </ul>
@endsection

@section('content')
<div class="row">
    <!-- Year Filter -->
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('tva.report') }}" class="d-flex align-items-end gap-3">
                    <div>
                        <label for="year" class="form-label">{{ __('Select Year') }}</label>
                        <select name="year" id="year" class="form-select" onchange="this.form.submit()">
                            @foreach($availableYears as $year)
                                <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="col-md-12 mb-4">
        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    {{ __('Total Invoices') }}
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($yearlyStats['total_invoices']) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-file-invoice fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    {{ __('Total TVA') }}
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($yearlyStats['total_tva_amount'], 2) }} MAD
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-percentage fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    {{ __('Total Revenue (TTC)') }}
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($yearlyStats['total_ttc_amount'], 2) }} MAD
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-coins fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    {{ __('Average Invoice Value (HT)') }}
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($yearlyStats['average_invoice_value'], 2) }} MAD
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="col-md-12 mb-4">
        <div class="row">
            <!-- Monthly TVA Chart -->
            <div class="col-xl-8 col-lg-7">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">{{ __('Monthly TVA Overview') }} - {{ $selectedYear }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-area">
                            <canvas id="monthlyTvaChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Clients Chart -->
            <div class="col-xl-4 col-lg-5">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">{{ __('Top 5 Clients by TVA') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-pie pt-4 pb-2">
                            <canvas id="topClientsChart"></canvas>
                        </div>
                        <div class="mt-4 text-center small">
                            @foreach($topClients as $client)
                                <span class="mr-2">
                                    <i class="fas fa-circle" style="color: {{ ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'][$loop->index % 5] }}"></i>
                                    {{ \Illuminate\Support\Str::limit($client['client_name'], 15) }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Car Statistics Section -->
    <div class="col-md-12 mb-4">
        <div class="row">
            <!-- Car Performance Stats Cards -->
            <div class="col-md-12 mb-4">
                <div class="row">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            {{ __('Total Unique Cars') }}
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ number_format($carPerformanceStats['total_unique_cars']) }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-car fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            {{ __('Total Rental Days') }}
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ number_format($carPerformanceStats['total_rental_days'], 0) }} {{ __('Days') }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            {{ __('Avg Rentals per Car') }}
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ number_format($carPerformanceStats['average_rentals_per_car'], 1) }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-chart-bar fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-danger shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                            {{ __('Most Rented Car') }}
                                        </div>
                                        <div class="h6 mb-0 font-weight-bold text-gray-800" title="{{ $carPerformanceStats['most_rented_car']['car_name'] ?? 'N/A' }}">
                                            {{ \Illuminate\Support\Str::limit($carPerformanceStats['most_rented_car']['car_name'] ?? 'N/A', 20) }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-trophy fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Car Charts Section -->
            <div class="col-xl-6 col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">{{ __('Top 5 Most Rented Cars') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-area">
                            <canvas id="topRentedCarsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">{{ __('Top 5 Most Profitable Cars (HT)') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-pie pt-4 pb-2">
                            <canvas id="topProfitableCarsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Statistics Table -->
    <div class="col-md-12 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">{{ __('Monthly Statistics') }} - {{ $selectedYear }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>{{ __('Month') }}</th>
                                <th>{{ __('Number of Invoices') }}</th>
                                <th>{{ __('Total HT') }}</th>
                                <th>{{ __('Total TVA') }}</th>
                                <th>{{ __('Total TTC') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($monthlyStats as $stats)
                                <tr>
                                    <td>{{ $stats['month_name'] }}</td>
                                    <td>{{ number_format($stats['count']) }}</td>
                                    <td>{{ number_format($stats['ht_amount'], 2) }} MAD</td>
                                    <td>{{ number_format($stats['tva_amount'], 2) }} MAD</td>
                                    <td>{{ number_format($stats['ttc_amount'], 2) }} MAD</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="font-weight-bold">
                                <td>{{ __('Total') }}</td>
                                <td>{{ number_format($yearlyStats['total_invoices']) }}</td>
                                <td>{{ number_format($yearlyStats['total_ht_amount'], 2) }} MAD</td>
                                <td>{{ number_format($yearlyStats['total_tva_amount'], 2) }} MAD</td>
                                <td>{{ number_format($yearlyStats['total_ttc_amount'], 2) }} MAD</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Clients Table -->
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">{{ __('Top Clients by TVA Amount') }} - {{ $selectedYear }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>{{ __('Rank') }}</th>
                                <th>{{ __('Client Name') }}</th>
                                <th>{{ __('Number of Invoices') }}</th>
                                <th>{{ __('Total TVA') }}</th>
                                <th>{{ __('Total TTC') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topClients as $client)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $client['client_name'] }}</td>
                                    <td>{{ number_format($client['count']) }}</td>
                                    <td>{{ number_format($client['total_tva'], 2) }} MAD</td>
                                    <td>{{ number_format($client['total_ttc'], 2) }} MAD</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Rented Cars Table -->
    <div class="col-md-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">{{ __('Top 5 Most Rented Cars') }} - {{ $selectedYear }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>{{ __('Rank') }}</th>
                                <th>{{ __('Car') }}</th>
                                <th>{{ __('Rental Count') }}</th>
                                <th>{{ __('Total Days') }}</th>
                                <th>{{ __('Avg Rental Value (HT)') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topRentedCars as $car)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td title="{{ $car['car_name'] }}">{{ \Illuminate\Support\Str::limit($car['car_name'], 25) }}</td>
                                    <td>{{ number_format($car['rental_count']) }}</td>
                                    <td>{{ number_format($car['total_rental_days'], 0) }}</td>
                                    <td>{{ number_format($car['average_rental_value_ht'], 2) }} MAD</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Profitable Cars Table -->
    <div class="col-md-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">{{ __('Top 5 Most Profitable Cars (HT)') }} - {{ $selectedYear }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>{{ __('Rank') }}</th>
                                <th>{{ __('Car') }}</th>
                                <th>{{ __('Revenue (HT)') }}</th>
                                <th>{{ __('TVA Amount') }}</th>
                                <th>{{ __('Total Revenue (TTC)') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topProfitableCars as $car)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td title="{{ $car['car_name'] }}">{{ \Illuminate\Support\Str::limit($car['car_name'], 25) }}</td>
                                    <td>{{ number_format($car['total_revenue_ht'], 2) }} MAD</td>
                                    <td>{{ number_format($car['total_tva'], 2) }} MAD</td>
                                    <td>{{ number_format($car['total_revenue_ttc'], 2) }} MAD</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script-page')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Monthly TVA Chart
    const monthlyTvaCtx = document.getElementById('monthlyTvaChart').getContext('2d');
    new Chart(monthlyTvaCtx, {
        type: 'line',
        data: {
            labels: @json($chartData['months']),
            datasets: [
                {
                    label: '{{ __("TVA Amount") }}',
                    data: @json($chartData['tva_amounts']),
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                },
                {
                    label: '{{ __("Total TTC") }}',
                    data: @json($chartData['ttc_amounts']),
                    borderColor: '#1cc88a',
                    backgroundColor: 'rgba(28, 200, 138, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString() + ' MAD';
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y.toLocaleString() + ' MAD';
                        }
                    }
                },
                legend: {
                    display: true,
                    position: 'top'
                }
            }
        }
    });

    // Top Clients Pie Chart
    const topClientsCtx = document.getElementById('topClientsChart').getContext('2d');
    new Chart(topClientsCtx, {
        type: 'doughnut',
        data: {
            labels: @json($topClients->pluck('client_name')->toArray()),
            datasets: [{
                data: @json($topClients->pluck('total_tva')->toArray()),
                backgroundColor: [
                    '#4e73df',
                    '#1cc88a',
                    '#36b9cc',
                    '#f6c23e',
                    '#e74a3b'
                ],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed.toLocaleString() + ' MAD';
                        }
                    }
                },
                legend: {
                    display: false
                }
            }
        }
    });

    // Top Rented Cars Chart
    const topRentedCarsCtx = document.getElementById('topRentedCarsChart').getContext('2d');
    new Chart(topRentedCarsCtx, {
        type: 'bar',
        data: {
            labels: @json($topRentedCars->pluck('car_name')->map(function($name) { return \Illuminate\Support\Str::limit($name, 20); })->toArray()),
            datasets: [{
                label: '{{ __("Rental Count") }}',
                data: @json($topRentedCars->pluck('rental_count')->toArray()),
                backgroundColor: [
                    'rgba(78, 115, 223, 0.8)',
                    'rgba(28, 200, 138, 0.8)',
                    'rgba(54, 185, 204, 0.8)',
                    'rgba(246, 194, 62, 0.8)',
                    'rgba(231, 74, 59, 0.8)'
                ],
                borderColor: [
                    '#4e73df',
                    '#1cc88a',
                    '#36b9cc',
                    '#f6c23e',
                    '#e74a3b'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y + ' {{ __("rentals") }}';
                        }
                    }
                },
                legend: {
                    display: false
                }
            }
        }
    });

    // Top Profitable Cars Chart
    const topProfitableCarsCtx = document.getElementById('topProfitableCarsChart').getContext('2d');
    new Chart(topProfitableCarsCtx, {
        type: 'doughnut',
        data: {
            labels: @json($topProfitableCars->pluck('car_name')->map(function($name) { return \Illuminate\Support\Str::limit($name, 20); })->toArray()),
            datasets: [{
                data: @json($topProfitableCars->pluck('total_revenue_ht')->toArray()),
                backgroundColor: [
                    '#4e73df',
                    '#1cc88a',
                    '#36b9cc',
                    '#f6c23e',
                    '#e74a3b'
                ],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed.toLocaleString() + ' MAD (HT)';
                        }
                    }
                },
                legend: {
                    display: false
                }
            }
        }
    });
</script>
@endpush

@push('css-page')
<style>
    .border-left-primary {
        border-left: 0.25rem solid #4e73df !important;
    }
    .border-left-success {
        border-left: 0.25rem solid #1cc88a !important;
    }
    .border-left-info {
        border-left: 0.25rem solid #36b9cc !important;
    }
    .border-left-warning {
        border-left: 0.25rem solid #f6c23e !important;
    }
    .border-left-danger {
        border-left: 0.25rem solid #e74a3b !important;
    }
    .chart-area {
        position: relative;
        height: 400px;
        width: 100%;
    }
    .chart-pie {
        position: relative;
        height: 300px;
        width: 100%;
    }
    .text-xs {
        font-size: 0.7rem;
    }
</style>
@endpush