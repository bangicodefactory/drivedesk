{{Form::open(array('route'=>array('booking.payment.store',$booking->id),'method'=>'post'))}}
<div class="modal-body">
    <div class="row">
        <div class="form-group">
            {{Form::label('date',__('Date'),array('class'=>'form-label')) }}
            {{Form::date('date',date('Y-m-d'),array('class'=>'form-control','required'=>'required'))}}
        </div>
        <div class="form-group">
            {{Form::label('amount',__('Amount'),array('class'=>'form-label')) }}
            {{Form::number('amount',$booking->getTotalDueAmount(),array('class'=>'form-control','placeholder'=>__('Enter payment amount'),'required'=>'required'))}}
        </div>
        <div class="form-group">
            {{ Form::label('payment_method', __('Method'),['class'=>'form-label']) }}
            {!! Form::select('payment_method', $paymentMethod,null,array('class' => 'form-control hidesearch ')) !!}
        </div>
        <div class="form-group">
            {{Form::label('notes',__('Notes'),array('class'=>'form-label')) }}
            {{Form::textarea('notes',null,array('class'=>'form-control','placeholder'=>__('Enter notes'),'rows'=>1))}}
        </div>
    </div>
</div>
<div class="modal-footer">
    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">{{__('Close')}}</button>
    {{Form::submit(__('Create'),array('class'=>'btn btn-primary ml-10'))}}
</div>
{{Form::close()}}

