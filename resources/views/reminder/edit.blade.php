{{ Form::model($reminder, array('route' => array('reminder.update', $reminder->id), 'enctype' => "multipart/form-data", 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{Form::label('name',__('Title'),array('class'=>'form-label')) }}
            {{Form::text('name',null,array('class'=>'form-control','placeholder'=>__('Enter reminder title'),'required'=>'required'))}}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('type', __('Reminder Type'),['class'=>'form-label']) }}
            {!! Form::select('type', $types,null,array('class' => 'form-control hidesearch ')) !!}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('vehicle', __('Vehicle'),['class'=>'form-label']) }}
            {!! Form::select('vehicle', $vehicles,null,array('class' => 'form-control hidesearch ')) !!}
        </div>

        <div class="form-group col-md-6">
            {{Form::label('reminder_date',__('Reminder Date'),array('class'=>'form-label')) }}
            {{Form::date('reminder_date',null,array('class'=>'form-control','required'=>'required'))}}
        </div>
        <div class="form-group col-md-12">
            {{Form::label('note',__('Notes'),array('class'=>'form-label')) }}
            {{Form::textarea('note',null,array('class'=>'form-control','placeholder'=>__('Reminder Description'),'rows'=>2))}}
        </div>
         <input name="status" id="status" hidden value="{{ $reminder->status }}">
    </div>
</div>
<div class="modal-footer">
    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">{{__('Close')}}</button>
    {{Form::submit(__('Update'),array('class'=>'btn btn-primary ml-10'))}}
</div>
{{Form::close()}}


