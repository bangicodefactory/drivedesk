{{ Form::model($inspectionType, array('route' => array('inspection-type.update', $inspectionType->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{Form::label('type',__('Type'),array('class'=>'form-label')) }}
            {{Form::text('type',null,array('class'=>'form-control','placeholder'=>__('Enter type'),'required'=>'required'))}}
        </div>
    </div>
</div>
<div class="modal-footer">
    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">{{__('Close')}}</button>
    {{Form::submit(__('Update'),array('class'=>'btn btn-primary ml-10'))}}
</div>
{{Form::close()}}


