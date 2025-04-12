{{ Form::open(['url' => 'rental-agreement', 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
{{-- driver section   --}}
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('driver', __('Driver'), ['class' => 'form-label']) }}
            {{-- {!! Form::select('driver', $drivers, null, ['class' => 'form-control hidesearch ', 'required' => 'required']) !!} --}}
            <select name="driver" id="driver" class="form-control basic-select" required>
                {{-- <option value="">{{ __('Select Driver') }}</option> --}}
                @foreach ($driversDropdown as $driverId => $driverName)
                    <option value="{{ $driverId }}">{{ $driverName }}</option>
                @endforeach
            </select>
        </div>
        
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('driver2', __('Driver2'), ['class' => 'form-label']) }}
            {{-- {!! Form::select('driver2', $drivers, null, ['class' => 'form-control hidesearch']) !!} --}}
            <select name="driver2" id="driver2" class="form-control basic-select">
                {{-- <option value="">{{ __('Select Driver') }}</option> --}}
                @foreach ($driversDropdown as $driverId => $driverName)
                    <option value="{{ $driverId }}">{{ $driverName }}</option>
                @endforeach
            </select>
        </div>
{{-- driver section  --}}

        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('vehicle', __('Vehicle'), ['class' => 'form-label']) }}
            <select name="vehicle" id="vehicle" class="form-control basic-select" required>
                <option value="">{{ __('Select Vehicle') }}</option>
                @foreach ($vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}">{{ $vehicle->name . ' - ' . $vehicle->license_plate }}</option>
                @endforeach
            </select>
        </div>
{{-- edit date   --}}
        {{-- <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('rental_start_date', __('Rental Start Date'), ['class' => 'form-label']) }}
            {{ Form::date('rental_start_date', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('rental_end_date', __('Rental End Date'), ['class' => 'form-label']) }}
            {{ Form::date('rental_end_date', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div> --}}
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('rental_start_date', __('Rental Start Date'), ['class' => 'form-label']) }}
            <div class="d-flex">
                {{ Form::date('rental_start_date', null, ['class' => 'form-control', 'required' => 'required']) }}
                {{ Form::time('rental_start_time', null, ['class' => 'form-control ms-2', 'required' => 'required']) }}
            </div>
        </div>
        
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('rental_end_date', __('Rental End Date'), ['class' => 'form-label']) }}
            <div class="d-flex">
                {{ Form::date('rental_end_date', null, ['class' => 'form-control', 'required' => 'required']) }}
                {{ Form::time('rental_end_time', null, ['class' => 'form-control ms-2', 'required' => 'required']) }}
            </div>
        </div>
        
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('rental_duration', __('Rental Duration (Days)'), ['class' => 'form-label']) }}
            {{ Form::number('rental_duration', null, ['class' => 'form-control', 'placeholder' => __('Enter rental duration'), 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
            {!! Form::select('status', $status, null, ['class' => 'form-control hidesearch ', 'required' => 'required']) !!}
        </div>
        <div class="form-group col-md-12 col-lg-12">
            {{ Form::label('terms_condition', __('Terms & Condition'), ['class' => 'form-label']) }}
            {{-- {{Form::textarea('terms_condition',null,array('class'=>'form-control','placeholder'=>__('Enter terms & condition'),'rows'=>7))}} --}}
            {{ Form::textarea('terms_condition', old('terms_condition', config('default_terms.rental_agreement')), [
                'class' => 'form-control',
                'placeholder' => __('Enter terms & condition'),
                'rows' => 7,
            ]) }}
        </div>
        <div class="form-group col-md-12 col-lg-12">
            {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
            {{ Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => __('Enter description'), 'rows' => 5]) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">{{ __('Close') }}</button>
    {{ Form::submit(__('Create'), ['class' => 'btn btn-primary ml-10']) }}
</div>
{{ Form::close() }}
