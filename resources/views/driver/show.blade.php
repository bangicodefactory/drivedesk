<div class="modal-body">
    <div class="product-card">
        <div class="row">
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{__('ID')}}</h6>
                    <p class="mb-20">{{ !empty($driver)?driverPrefix().$driver->driver_id:'-' }}</p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{__('First Name')}}</h6>
                    <p class="mb-20">{{$user->first_name}}</p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{__('Last Name')}}</h6>
                    <p class="mb-20">{{$user->last_name}}</p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{__('Email')}}</h6>
                    <p class="mb-20">{{$user->email}}</p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{__('Phone Number')}}</h6>
                    <p class="mb-20">{{!empty($user->phone_number)?$user->phone_number:'-'}}</p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{__('Gender')}}</h6>
                    <p class="mb-20">{{!empty($driver)?$driver->gender:'-'}}</p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{__('Age')}}</h6>
                    <p class="mb-20">{{!empty($driver) && $driver->age!=0?$driver->age:'-'}}</p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{__('Address')}}</h6>
                    <p class="mb-20">{{!empty($driver) && !empty($driver->address)?$driver->address:'-'}}</p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{__('Birth Date')}}</h6>
                    <p class="mb-20">{{ !empty($driver) && !empty($driver->birth_date)?dateFormat($driver->birth_date):'-' }} </p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{__('License Number')}}</h6>
                    <p class="mb-20">{{!empty($driver) && !empty($driver->license_number)?$driver->license_number:'-'}}</p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{__('Issue Date')}}</h6>
                    <p class="mb-20">{{ !empty($driver) && !empty($driver->issue_date)?dateFormat($driver->issue_date):'-'}}</p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{__('Expiration Date')}}</h6>
                    <p class="mb-20">{{!empty($driver) && !empty($driver->expiration_date)?dateFormat($driver->expiration_date):'-' }}</p>
                </div>
            </div>
            
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{__('License 1:')}}</h6>
                    <p class="mb-20">
                        @if(!empty($driver) && !empty($driver->license))
                            <a href="{{asset(Storage::url('upload/license'.'/'.$driver->license))}}"
                               target="_blank"> {{!empty($driver)?$driver->license:'-'}}</a>
                        @else
                            -
                        @endif
                    </p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{__('License 2:')}}</h6>
                    <p class="mb-20">
                        @if(!empty($driver) && !empty($driver->license_1))
                            <a href="{{asset(Storage::url('upload/license'.'/'.$driver->license_1))}}"
                               target="_blank"> {{!empty($driver)?$driver->license_1:'-'}}</a>
                        @else
                            -
                        @endif
                    </p>
                </div>
            </div>
            <div class="col-12">
                <div class="detail-group">
                    <h6>{{__('Reference')}}</h6>
                    <p class="mb-20">{{!empty($driver) && !empty($driver->reference)?$driver->reference:'-'}}</p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{__('ID file 1:')}}</h6>
                    <p class="mb-20">
                        @if(!empty($driver) && !empty($driver->document))
                            <a href="{{asset(Storage::url('upload/document'.'/'.$driver->document))}}"
                               target="_blank">{{$driver->document}}</a>
                        @else
                            -
                        @endif
                    </p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{__('ID file 2:')}}</h6>
                    <p class="mb-20">
                        @if(!empty($driver) && !empty($driver->document_1))
                            <a href="{{asset(Storage::url('upload/document'.'/'.$driver->document_1))}}"
                               target="_blank">{{$driver->document_1}}</a>
                        @else
                            -
                        @endif
                    </p>
                </div>
            </div>
            <div class="col-6">
                <div class="detail-group">
                    <h6>{{__('notes')}}</h6>
                    <p class="mb-20">{{!empty($driver) && !empty($driver->notes)?$driver->notes:'-'}}</p>
                </div>
            </div>
            <div class="col-12">
                <div class="detail-group">
                    <h6>{{__('ICE_company')}}</h6>
                    <p class="mb-20">{{!empty($driver) && !empty($driver->ICE_company)?$driver->ICE_company:'-'}}</p>
                </div>
            </div>
        </div>
    </div>
</div>



