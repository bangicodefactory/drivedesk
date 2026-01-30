{{ Form::model($credit, ['url' => route('credit.update', $credit), 'method' => 'PUT', 'id' => 'credit-edit-form']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('driver_id', __('credit.driver'), ['class' => 'form-label']) }}
            <select name="driver_id" id="credit_edit_driver_id" class="form-control basic-select" required>
                <option value="">{{ __('credit.search_and_select_driver') }}</option>
                @foreach ($drivers as $d)
                    <option value="{{ $d->id }}" {{ (old('driver_id', $credit->driver_id) == $d->id) ? 'selected' : '' }}>
                        {{ $d->name }} @if($d->phone_number) ({{ $d->phone_number }}) @endif
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('amount', __('credit.amount'), ['class' => 'form-label']) }}
            {{ Form::number('amount', null, ['class' => 'form-control', 'placeholder' => __('credit.amount'), 'step' => '0.01', 'min' => '0', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('status', __('credit.status'), ['class' => 'form-label']) }}
            {{ Form::select('status', $statuses, null, ['class' => 'form-control basic-select']) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('credit_date', __('credit.date_credit'), ['class' => 'form-label']) }}
            {{ Form::date('credit_date', $credit->credit_date ? $credit->credit_date->format('Y-m-d') : null, ['class' => 'form-control']) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">{{ __('credit.close') }}</button>
    {{ Form::submit(__('credit.update_credit'), ['class' => 'btn btn-primary']) }}
</div>
{{ Form::close() }}
