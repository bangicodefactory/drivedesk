{{Form::open(array('url'=>'reminder','method'=>'post', 'enctype' => "multipart/form-data"))}}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{Form::label('name',__('Title'),array('class'=>'form-label')) }}
            {{Form::text('name',null,array('class'=>'form-control','placeholder'=>__('Enter reminder titel'),'required'=>'required'))}}
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
    </div>
</div>
<div class="modal-footer">
    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">{{__('Close')}}</button>
    {{Form::submit(__('Create'),array('class'=>'btn btn-primary ml-10'))}}
</div>
{{Form::close()}}

