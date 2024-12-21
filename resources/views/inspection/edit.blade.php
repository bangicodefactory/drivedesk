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
                {{__('Edit')}}
            </a>
        </li>
    </ul>
@endsection
@section('card-action-btn')

@endsection
@section('content')
    {{ Form::model($inspection, array('route' => array('inspection.update', $inspection->id), 'method' => 'PUT','enctype' => "multipart/form-data")) }}
    <div class="row">
        <div class="col-md-6 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4>{{__('Inspection Details')}}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-6 col-lg-6">
                            {{ Form::label('vehicle', __('Vehicle'),['class'=>'form-label']) }}
                            {!! Form::select('vehicle', $vehicles,null,array('class' => 'form-control hidesearch ','required'=>'required')) !!}
                        </div>
                        <div class="form-group col-md-6 col-lg-6">
                            {{ Form::label('inspector ', __('Inspection By'),['class'=>'form-label']) }}
                            {{Form::text('inspector',null,array('class'=>'form-control','required'=>'required'))}}
                        </div>
                        <div class="form-group col-md-6 col-lg-6">
                            {{Form::label('inspection_date',__('Inspection Date'),array('class'=>'form-label')) }}
                            {{Form::date('inspection_date',null,array('class'=>'form-control','required'=>'required'))}}
                        </div>
                        <div class="form-group col-md-6 col-lg-6">
                            {{ Form::label('status', __('Inspection Status'),['class'=>'form-label']) }}
                            {!! Form::select('status', $status,null,array('class' => 'form-control hidesearch ','required'=>'required')) !!}
                        </div>
                        <div class="form-group col-md-6 col-lg-6">
                            {{ Form::label('repair_status', __('Repair Status'),['class'=>'form-label']) }}
                            {!! Form::select('repair_status', $repairStatus,null,array('class' => 'form-control hidesearch ','required'=>'required')) !!}
                        </div>
                        <div class="form-group col-md-6 col-lg-6">
                            {{Form::label('notes',__('Notes'),array('class'=>'form-label')) }}
                            {{Form::textarea('notes',null,array('class'=>'form-control','placeholder'=>__('Enter notes'),'rows'=>2,'required'=>'required'))}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-6">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-6 col-lg-6">
                                    {{ Form::label('amount', __('Amount'),['class'=>'form-label']) }}
                                    {{Form::number('amount',null,array('class'=>'form-control','placeholder'=>__('Enter amount'),'required'=>'required'))}}
                                </div>
                                <div class="form-group col-md-6 col-lg-6">
                                    {{Form::label('receipt',__('Receipt'),array('class'=>'form-label')) }}
                                    {{Form::file('receipt',array('class'=>'form-control'))}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>{{__('Incoming Details')}}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-6 col-lg-6">
                                    {{Form::label('incoming_date',__('Date'),array('class'=>'form-label')) }}
                                    {{Form::date('incoming_date',null,array('class'=>'form-control'))}}
                                </div>

                                <div class="form-group col-md-6 col-lg-6">
                                    {{Form::label('meter_reading_incoming',__('Meter Reading (km)'),array('class'=>'form-label')) }}
                                    {{Form::number('meter_reading_incoming',null,array('class'=>'form-control','placeholder'=>__('Enter meter reading incoming (km)')))}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
                        @foreach($types as $type)
                            <div class="col-md-6 col-lg-6">
                                <h6 class="form-label">{{$type->type}}</h6>
                                <div class="col-md-12 col-lg-12">
                                    <div class="row">
                                        <div class="form-group col-auto">
                                            <label class="switch with-icon switch-primary">
                                                <input type="checkbox" name="types[{{$type->id}}][type]" {{isset($details[$type->id]['type']) && !empty($details[$type->id]['type'])?'checked':''}}><span
                                                    class="switch-btn"></span>
                                            </label>
                                        </div>
                                        <div class="form-group col">
                                            <input class="form-control" type="text" placeholder="{{__('Enter notes')}}" name="types[{{$type->id}}][note]" value="{{isset($details[$type->id]['note']) && !empty($details[$type->id]['note'])?$details[$type->id]['note']:''}}" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row text-end">
        <div class="col-md-12 col-lg-12">
            {{Form::submit(__('Update'),array('class'=>'btn btn-primary ml-10'))}}
        </div>
    </div>
    {{ Form::close() }}
@endsection
