@extends('layouts.app')

@section('page-title')
    {{ __('Edit TVA') }}
@endsection

@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><h1>{{ __('Dashboard') }}</h1></a></li>
        <li class="breadcrumb-item"><a href="{{ route('tva.index') }}">{{ __('TVA List') }}</a></li>
        <li class="breadcrumb-item active">{{ __('Edit TVA') }}</li>
    </ul>
@endsection

@section('content')
    {{ Form::model($tva, ['route' => ['tva.update', $tva->id], 'method' => 'PUT']) }}
    <div class="row">
        {{-- Booking select --}}
        <div class="form-group col-md-6">
            {{ Form::label('booking_id', __('Booking'), ['class' => 'form-label']) }}
            <input type="text" class="form-control" value="{{ $tva->booking_id ?? 'N/A' }}" readonly>
            <input type="hidden" name="booking_id" value="{{ $tva->booking_id }}">
        </div>
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('designation', __('Vehicle'), ['class' => 'form-label']) }}
            {{ Form::text('designation', null, ['class' => 'form-control']) }}
        </div>

        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('facture_number', __('Facture Number'), ['class' => 'form-label']) }}
            {{ Form::text('facture_number', bookingPrefix() . $tva->facture_number, ['class' => 'form-control', 'required' => true, 'readonly' => true]) }}
        </div>
        {{-- Facture Date --}}
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('facture_date', __('Facture Date'), ['class' => 'form-label']) }}
            {{ Form::date('facture_date', $tva->facture_date ? \Carbon\Carbon::parse($tva->facture_date)->format('Y-m-d') : null, ['class' => 'form-control', 'required' => true]) }}
        </div>
        {{-- Quantity --}}
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('quantity', __('Duration'), ['class' => 'form-label']) }}
            {{ Form::number('quantity', null, ['class' => 'form-control', 'step' => '1', 'readonly' => true]) }}
        </div>

        {{-- Total HT --}}
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('total_ht', __('Total HT'), ['class' => 'form-label']) }}
            {{ Form::number('total_ht', null, ['class' => 'form-control', 'step' => '0.01', 'readonly' => true]) }}
        </div>

        {{-- TVA --}}
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('tva', __('TVA'), ['class' => 'form-label']) }}
            {{ Form::number('tva', null, ['class' => 'form-control', 'step' => '0.01', 'readonly' => true]) }}
        </div>

        {{-- Unit Price HT --}}
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('unit_price_ht', __('Unit Price HT'), ['class' => 'form-label']) }}
            {{ Form::number('unit_price_ht', null, ['class' => 'form-control', 'step' => '0.01', 'readonly' => true]) }}
        </div>


        {{-- Montant TTC --}}
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('montant_ttc', __('Montant TTC'), ['class' => 'form-label']) }}
            {{ Form::number('montant_ttc', null, ['class' => 'form-control', 'step' => '0.01']) }}
        </div>
    </div>

    <div class="mt-4 text-end">
        {{ Form::submit(__('Update TVA'), ['class' => 'btn btn-primary']) }}
    </div>
    {{ Form::close() }}
@endsection
