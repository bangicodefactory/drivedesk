<div class="modal-body">
    <div class="product-card">
        <div class="row">
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{__('Vehicle ID')}}</h6>
                    <p class="mb-20">{{ vehiclePrefix().$vehicle->vehicle_id}}</p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{__('Vehicle Type')}}</h6>
                    <p class="mb-20">{{!empty($vehicle->types)?$vehicle->types->type:'-'}}</p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{__('Vehicle Name')}}</h6>
                    <p class="mb-20">{{$vehicle->name}}</p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{__('Vehicle Model')}}</h6>
                    <p class="mb-20">{{$vehicle->model}}</p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{__('Engine Type')}}</h6>
                    <p class="mb-20">{{$vehicle->engine_type}}</p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{__('Engine Number')}}</h6>
                    <p class="mb-20">{{!empty($vehicle->engine_no)?$vehicle->engine_no:'-'}}</p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{__('License Plate')}}</h6>
                    <p class="mb-20">{{$vehicle->license_plate}}</p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{__('Registration Expiry Date')}}</h6>
                    <p class="mb-20">{{ !empty($vehicle->registration_expiry_date)?dateFormat($vehicle->registration_expiry_date):'-' }}</p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{__('Daily Rate')}}</h6>
                    <p class="mb-20">{{priceFormat($vehicle->daily_rate)}}</p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{__('Year of First Immatriculation')}}</h6>
                    <p class="mb-20">{{$vehicle->year_of_ﬁrst_immatriculation!=0 && $vehicle->year_of_ﬁrst_immatriculation!=0000?$vehicle->year_of_ﬁrst_immatriculation:'-'}}</p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{__('Gearbox')}}</h6>
                    <p class="mb-20">{{\App\Models\Vehicle::$gearbox[$vehicle->gearbox]}}</p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{__('Fuel Type')}}</h6>
                    <p class="mb-20">{{App\Models\Vehicle::$fuelType[$vehicle->fuel_type]}}</p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{__('Number of Seats')}}</h6>
                    <p class="mb-20">{{$vehicle->number_of_seats}}</p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{__('Kilometer')}}</h6>
                    <p class="mb-20">{{$vehicle->kilometers}}</p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{__('Options')}}</h6>
                    <p class="mb-20">
                        @if(!empty($vehicle->options()))
                        @foreach($vehicle->options() as $option )
                            {{$option->name}} <br>
                        @endforeach
                        @else
                            -
                        @endif
                    </p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{__('Document')}}</h6>
                    <p class="mb-20">
                        @if(!empty($vehicle) && !empty($vehicle->document))
                            <a href="{{asset(Storage::url('upload/document'.'/'.$vehicle->document))}}"
                               target="_blank">{{$vehicle->document}}</a>
                        @else
                            -
                        @endif
                    </p>
                </div>
            </div>
            <div class="col-12">
                <div class="detail-group">
                    <h6>{{__('Notes')}}</h6>
                    <p class="mb-20">{{!empty($vehicle->notes)?$vehicle->notes:'-'}}</p>
                </div>
            </div>
        </div>
    </div>
</div>



