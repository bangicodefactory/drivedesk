{{ Form::model($credit, ['url' => route('credit.update', $credit), 'method' => 'PUT', 'id' => 'credit-edit-form']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('driver_id', __('Driver'), ['class' => 'form-label']) }}
            <select name="driver_id" id="credit_edit_driver_id" class="form-control basic-select" required>
                <option value="">{{ __('Search and select a driver') }}</option>
                @foreach ($drivers as $d)
                    <option value="{{ $d->id }}" {{ (old('driver_id', $credit->driver_id) == $d->id) ? 'selected' : '' }}>
                        {{ $d->name }} @if($d->email) ({{ $d->email }}) @endif
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('amount', __('Amount'), ['class' => 'form-label']) }}
            {{ Form::number('amount', null, ['class' => 'form-control', 'placeholder' => __('Amount'), 'step' => '0.01', 'min' => '0', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
            {{ Form::select('status', $statuses, null, ['class' => 'form-control basic-select']) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('credit_date', __('Date credit'), ['class' => 'form-label']) }}
            {{ Form::date('credit_date', $credit->credit_date ? $credit->credit_date->format('Y-m-d') : null, ['class' => 'form-control']) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">{{ __('Close') }}</button>
    {{ Form::submit(__('Update Credit'), ['class' => 'btn btn-primary']) }}
</div>
{{ Form::close() }}
