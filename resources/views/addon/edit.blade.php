{{ Form::model($addon, array('route' => array('addon.update', $addon->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{Form::label('name',__('Addon'),array('class'=>'form-label')) }}
            {{Form::text('name',null,array('class'=>'form-control','placeholder'=>__('Enter addon name'),'required'=>'required'))}}
        </div>
        <div class="form-group col-md-12">
            {{Form::label('price',__('Price'),array('class'=>'form-label')) }}
            {{Form::number('price',null,array('class'=>'form-control','placeholder'=>__('Enter price'),'required'=>'required'))}}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('billing_type', __('Billing Type'),['class'=>'form-label']) }}
            <select name="billing_type" class="form-control hidesearch " id="billing_type">
                @foreach($billingType as $k=>$val)
                    <option value="{{$k}}">{{$val}}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">{{__('Close')}}</button>
    {{Form::submit(__('Update'),array('class'=>'btn btn-primary ml-10'))}}
</div>
{{Form::close()}}


