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
@extends('layouts.app')
@section('page-title')
    {{__('Dashboard')}}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{route('dashboard')}}"><h1>{{__('Dashboard')}}</h1></a>
        </li>
    </ul>
@endsection
@php
    $settings=settings();
@endphp
@section('content')
    <div class="row">
        <div class="col-xxl-3 col-sm-6 cdx-xxl-50">
            <div class="card sale-revenue">
                <div class="card-header">
                    <h4>{{__('Total Driver')}}</h4>
                </div>
                <div class="card-body progressCounter">
                    <h2>
                        <span class="">
                            {{$result['totalDriver']}}
                        </span>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-sm-6 cdx-xxl-50">
            <div class="card sale-revenue">
                <div class="card-header">
                    <h4>{{__('Total Booking')}}</h4>
                </div>
                <div class="card-body progressCounter">
                    <h2>
                        <span class="">{{$result['totalBooking']}}</span>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-sm-6 cdx-xxl-50">
            <div class="card sale-revenue">
                <div class="card-header">
                    <h4>{{__('Total Income')}}</h4>
                </div>
                <div class="card-body progressCounter">
                    <h2>
                        <span class="">{{priceFormat($result['totalIncome'])}}</span>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-sm-6 cdx-xxl-50">
            <div class="card sale-revenue">
                <div class="card-header">
                    <h4>{{__('Total Expense')}}</h4>
                </div>
                <div class="card-body progressCounter">
                    <h2>
                        <span class="">{{priceFormat($result['totalExpense'])}}</span>
                    </h2>
                </div>
            </div>
        </div>
{{-- add notification bar  --}}
<div class="col-12">
    <div class="card shadow-sm">
        <div class="card-header  py-3" style="background-color: #197CBC;">
            <div class="text-center mb-2">
                <h5 class="card-title mb-0 fw-bolder " style="color: #f8f9fa;">Notifications</h5>
            </div>
            <div class="text-end">
                <span class="badge text-bg-warning">{{ count($reminders) }} New</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                @forelse($reminders as $reminder)
                    <div class="list-group-item p-4 border-bottom">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <h6 class="mb-1  text-dark">{{ $reminder->vehicles->name ?? 'N/A' }}</h6>
                                <p class="mb-0 text-muted">
                                    <i class="fas fa-hashtag me-2"></i>{{ $reminder->vehicles->license_plate ?? 'N/A' }}
                                </p>
                            </div>
                            <div class="col-md-5">
                                <p class="mb-1 text-muted">{{ $reminder->note ?? 'No description' }}</p>
                                <p class="mb-0">
                                    <i class="far fa-calendar me-2"></i>
                                    {{ \Carbon\Carbon::parse($reminder->reminder_date)->format('M d, Y') }}
                                </p>
                            </div>
                            <div class="col-md-3 text-md-end">
                                <span class="badge bg-{{ $reminder->status === 'urgent' ? 'danger' : 'warning' }} px-3 py-2">
                                    {{ ucfirst($reminder->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="list-group-item p-4 text-center">No notifications found</div>
                @endforelse
            </div>
        </div>
        @if(count($reminders) > 0)
                <div class="card-footer text-center py-3">
                    <a href="{{ route('reminder.index') }}" class="text-primary text-decoration-none">View All</a>
                </div>
            @endif
    </div>
</div>
{{-- end notification bar  --}}
        <div class="col-xxl-12 cdx-xxl-50">
            <div class="card overall-revenuetbl">
                <div class="card-header">
                    <h4>{{__('Income Vs Expense')}}</h4>
                </div>
                <div class="card-body">
                    <div id="incomeExpense"></div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script-page')
    <script>
        var options = {

            series: [{
                name: "{{__('Income')}}",
                type: 'column',
                data: {!! json_encode($result['incomeExpenseByMonth']['income']) !!},
            }, {
                name: " {{__('Expense')}}",
                type: 'area',
                data: {!! json_encode($result['incomeExpenseByMonth']['expense']) !!},
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
                        return  "{{$result['settings']['CURRENCY_SYMBOL']}}" + y.toFixed(0);
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
                categories: {!! json_encode($result['incomeExpenseByMonth']['label']) !!} ,
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
@endpush
