{{ Form::model($rentalAgreement, array('route' => array('rental-agreement.update', $rentalAgreement->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('driver', __('Driver'),['class'=>'form-label']) }}
            {!! Form::select('driver', $drivers,null,array('class' => 'form-control hidesearch ','required'=>'required')) !!}
        </div>
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('vehicle', __('Vehicle'),['class'=>'form-label']) }}
            <select name="vehicle" id="vehicle" class="form-control basic-select" required>
                <option value="">{{__('Select Vehicle')}}</option>
                @foreach($vehicles as $vehicle)
                    <option
                        value="{{$vehicle->id}}" {{$rentalAgreement->vehicle==$vehicle->id?'selected':''}}>{{$vehicle->name.' - '.$vehicle->license_plate}}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group col-md-6 col-lg-6">
            {{Form::label('rental_start_date',__('Rental Start Date'),array('class'=>'form-label')) }}
            {{Form::date('rental_start_date',null,array('class'=>'form-control','required'=>'required'))}}
        </div>
        <div class="form-group col-md-6 col-lg-6">
            {{Form::label('rental_end_date',__('Rental End Date'),array('class'=>'form-label')) }}
            {{Form::date('rental_end_date',null,array('class'=>'form-control','required'=>'required'))}}
        </div>
        <div class="form-group col-md-6 col-lg-6">
            {{Form::label('rental_duration',__('Rental Duration (Days)'),array('class'=>'form-label')) }}
            {{Form::number('rental_duration',null,array('class'=>'form-control','placeholder'=>__('Enter rental duration'),'required'=>'required'))}}
        </div>
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('status', __('Status'),['class'=>'form-label']) }}
            {!! Form::select('status', $status,null,array('class' => 'form-control hidesearch ','required'=>'required')) !!}
        </div>
        <div class="form-group col-md-12 col-lg-12">
            {{Form::label('terms_condition',__('Terms & Condition'),array('class'=>'form-label')) }}
            {{Form::textarea('terms_condition',null,array('class'=>'form-control','placeholder'=>__('Enter terms & condition'),'rows'=>5))}}
        </div>
        <div class="form-group col-md-12 col-lg-12">
            {{Form::label('description',__('Description'),array('class'=>'form-label')) }}
            {{Form::textarea('description',null,array('class'=>'form-control','placeholder'=>__('Enter description'),'rows'=>5))}}
        </div>
    </div>
</div>
<div class="modal-footer">
    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">{{__('Close')}}</button>
    {{Form::submit(__('Update'),array('class'=>'btn btn-primary ml-10'))}}
</div>
{{Form::close()}}


