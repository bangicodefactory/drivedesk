{{ Form::open(['url' => 'vehicle', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('name', __('Vehicle Name'), ['class' => 'form-label']) }}
            {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter vehicle name'), 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('type', __('Type'), ['class' => 'form-label']) }}
            {!! Form::select('type', $types, null, ['class' => 'form-control hidesearch ', 'required' => 'required']) !!}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('model', __('Model'), ['class' => 'form-label']) }}
            {{ Form::text('model', null, ['class' => 'form-control', 'placeholder' => __('Enter model'), 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('engine_type', __('Engine Type'), ['class' => 'form-label']) }}
            {{ Form::text('engine_type', null, ['class' => 'form-control', 'placeholder' => __('Enter engine type'), 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('engine_no', __('Engine Number'), ['class' => 'form-label']) }}
            {{ Form::text('engine_no', null, ['class' => 'form-control', 'placeholder' => __('Enter engine number')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('license_plate', __('License Plate'), ['class' => 'form-label']) }}
            {{ Form::text('license_plate', null, ['class' => 'form-control', 'placeholder' => __('Enter license plate'), 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('registration_expiry_date', __('Registration Expiry Date'), ['class' => 'form-label']) }}
            {{ Form::date('registration_expiry_date', null, ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('daily_rate', __('Daily Rate'), ['class' => 'form-label']) }}
            {{ Form::number('daily_rate', null, ['class' => 'form-control', 'placeholder' => __('Enter daily rate'), 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('year_of_ﬁrst_immatriculation', __('Year of First Immatriculation'), ['class' => 'form-label']) }}
            {{ Form::number('year_of_ﬁrst_immatriculation', null, ['class' => 'form-control', 'placeholder' => __('Enter Year of First Immatriculation')]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('gearbox', __('Gearbox'), ['class' => 'form-label']) }}
            <select name="gearbox" class="form-control hidesearch " id="gearbox" required>
                @foreach ($gearbox as $k => $val)
                    <option value="{{ $k }}">{{ $val }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('fuel_type', __('Fuel Type'), ['class' => 'form-label']) }}
            <select name="fuel_type" class="form-control hidesearch " id="fuel_type" required>
                @foreach ($fuelType as $k => $val)
                    <option value="{{ $k }}">{{ $val }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('number_of_seats', __('Number of Seats'), ['class' => 'form-label', 'required' => 'required']) }}
            {{ Form::number('number_of_seats', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('kilometers', __('Kilometer'), ['class' => 'form-label', 'required' => 'required']) }}
            {{ Form::number('kilometers', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('option', __('Options'), ['class' => 'form-label']) }}
            {!! Form::select('option[]', $option, null, ['class' => 'form-control hidesearch ', 'multiple']) !!}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('document', __('Document'), ['class' => 'form-label']) }}
            {{ Form::file('document', ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('picture', __('Photo de voiture'), ['class' => 'form-label']) }}
            {{ Form::file('picture', ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('notes', __('Notes'), ['class' => 'form-label']) }}
            {{ Form::textarea('notes', null, ['class' => 'form-control', 'placeholder' => __('Enter notes'), 'rows' => 1]) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">{{ __('Close') }}</button>
    {{ Form::submit(__('Create'), ['class' => 'btn btn-primary ml-10']) }}
</div>
{{ Form::close() }}
