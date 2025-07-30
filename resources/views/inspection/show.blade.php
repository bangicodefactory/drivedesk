@extends('layouts.app')
@section('page-title')
    {{__('Inspection')}}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{route('dashboard')}}"><h1>{{__('Dashboard')}}</h1></a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{route('inspection.index')}}">
                {{__('Inspection')}}
            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">
                {{__('Details')}}
            </a>
        </li>
    </ul>
@endsection
@section('card-action-btn')
    <a class="btn btn-secondary print" href="javascript:void(0);"><i class="fa fa-print"></i> {{__('Print')}}</a>
@endsection
@section('content')
    <div id="invoice-print">
        <div class="row">
            <div class="col-md-4 col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h4>{{__('Inspection Details')}}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="detail-group">
                                    <h6>{{__('Vehicle')}}</h6>
                                    <p class="mb-20">{{ !empty($inspection->vehicles)?$inspection->vehicles->name:'-' }} </p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="detail-group">
                                    <h6>{{__('Inspection By')}}</h6>
                                    <p class="mb-20">{{ $inspection->inspector}} </p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="detail-group">
                                    <h6>{{__('Inspection Date')}}</h6>
                                    <p class="mb-20">{{ dateFormat($inspection->inspection_date) }}  </p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="detail-group">
                                    <h6>{{__('Inspection Status')}}</h6>
                                    <p class="mb-20">

                                        @if($inspection->status=='pending' || $inspection->status=='on_hold')
                                            <span
                                                class="badge badge-warning">{{\App\Models\Inspection::$status[$inspection->status]}}</span>
                                        @elseif($inspection->status=='completed' || $inspection->status=='conditional_pass')
                                            <span
                                                class="badge badge-success">{{\App\Models\Inspection::$status[$inspection->status]}}</span>
                                        @elseif($inspection->status=='in_progress')
                                            <span
                                                class="badge badge-primary">{{\App\Models\Inspection::$status[$inspection->status]}}</span>
                                        @elseif($inspection->status=='reject')
                                            <span
                                                class="badge badge-danger">{{\App\Models\Inspection::$status[$inspection->status]}}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="detail-group">
                                    <h6>{{__('Repair Status')}}</h6>
                                    <p class="mb-20">
                                        @if($inspection->repair_status=='pending' || $inspection->repair_status=='on_hold')
                                            <span
                                                class="badge badge-warning">{{\App\Models\Inspection::$repairStatus[$inspection->repair_status]}}</span>
                                        @elseif($inspection->repair_status=='completed')
                                            <span
                                                class="badge badge-success">{{\App\Models\Inspection::$repairStatus[$inspection->repair_status]}}</span>
                                        @elseif($inspection->repair_status=='in_progress')
                                            <span
                                                class="badge badge-primary">{{\App\Models\Inspection::$repairStatus[$inspection->repair_status]}}</span>
                                        @elseif($inspection->repair_status=='needs_repair')
                                            <span
                                                class="badge badge-danger">{{\App\Models\Inspection::$repairStatus[$inspection->repair_status]}}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="detail-group">
                                    <h6>{{__('Amount')}}</h6>
                                    <p class="mb-20">{{ priceFormat($inspection->amount) }}  </p>
                                </div>
                            </div>
                            @if(!empty($inspection->receipt))
                                <div class="col-6">
                                    <div class="detail-group">
                                        <h6>{{__('Receipt')}}</h6>
                                        <p class="mb-20">
                                            <a class="text-primary"
                                               href="{{asset('/storage/upload/expense/'.$inspection->receipt)}} "
                                               target="_blank" data-bs-toggle="tooltip"
                                               data-bs-original-title="{{__('Receipt')}}"> <i data-feather="file"></i>
                                            </a>
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8 col-lg-8">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>{{__('Incoming Details')}}</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="detail-group">
                                            <h6>{{__('Date')}}</h6>
                                            <p class="mb-20">{{!empty($inspection->incoming_date)? dateFormat($inspection->incoming_date):'-'}} </p>
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="detail-group">
                                            <h6>{{__('Meter Reading (Km)')}}</h6>
                                            <p class="mb-20">{{ !empty($inspection->meter_reading_incoming)?$inspection->meter_reading_incoming:'-'}} </p>
                                        </div>
                                    </div>


                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @if(!empty($inspection->notes))
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="detail-group">
                                        <h6>{{__('Notes')}}</h6>
                                        <p class="mb-20">{{ $inspection->notes}} </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4>{{__('Inspections Checklist')}}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($details as $type)
                                <div class="col-6">
                                    <div class="detail-group">
                                        <h6>
                                            @if(isset($type['status']) && $type['status']=='on')
                                                <i data-feather="check" class="text-success"></i>
                                            @else
                                                <i data-feather="x" class="text-danger"></i>
                                            @endif
                                            {{$type['type']}} </h6>
                                        <p class="mb-20">{{ $type['note'] }} </p>
                                    </div>
                                </div>
                            @endforeach

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script-page')
    <script>
        $(document).on('click', '.print', function () {
            var printContents = document.getElementById('invoice-print').innerHTML;
            var originalContents = document.body.innerHTML;

            document.body.innerHTML = printContents;

            window.print();

            document.body.innerHTML = originalContents;

        });
    </script>
@endpush
