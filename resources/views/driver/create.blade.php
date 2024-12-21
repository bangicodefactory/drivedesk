{{Form::open(array('url'=>'driver','method'=>'post', 'enctype' => "multipart/form-data"))}}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{Form::label('first_name',__('First Name'),array('class'=>'form-label')) }}
            {{Form::text('first_name',null,array('class'=>'form-control','placeholder'=>__('Enter First Name'),'required'=>'required'))}}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('last_name',__('Last Name'),array('class'=>'form-label')) }}
            {{Form::text('last_name',null,array('class'=>'form-control','placeholder'=>__('Enter First Name'),'required'=>'required'))}}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('email',__('Email'),array('class'=>'form-label'))}}
            {{Form::text('email',null,array('class'=>'form-control','placeholder'=>__('Enter Email'),'required'=>'required'))}}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('phone_number',__('Phone Number'),array('class'=>'form-label')) }}
            {{Form::text('phone_number',null,array('class'=>'form-control','placeholder'=>__('Enter Phone Number'),'required'=>'required'))}}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('gender', __('Gender'),['class'=>'form-label']) }}
            {!! Form::select('gender', $gender,null,array('class' => 'form-control hidesearch ','required'=>'required')) !!}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('Age',__('age'),array('class'=>'form-label')) }}
            {{Form::number('age',null,array('class'=>'form-control','placeholder'=>__('Enter age')))}}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('birth_date',__('Birth date'),array('class'=>'form-label')) }}
            {{Form::date('birth_date',null,array('class'=>'form-control','required'=>'required'))}}
        </div>
        <div class="form-group col-md-12">
            {{Form::label('address',__('Address'),array('class'=>'form-label')) }}
            {{Form::textarea('address',null,array('class'=>'form-control','placeholder'=>__('Enter address'),'required'=>'required','rows'=>1))}}
        </div>

        <div class="form-group col-md-6">
            {{Form::label('license_number',__('License Number'),array('class'=>'form-label')) }}
            {{Form::text('license_number',null,array('class'=>'form-control','placeholder'=>__('Enter license number'),'required'=>'required'))}}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('issue_date',__('Issue Date'),array('class'=>'form-label')) }}
            {{Form::date('issue_date',null,array('class'=>'form-control','required'=>'required'))}}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('expiration_date',__('Expiration Date'),array('class'=>'form-label')) }}
            {{Form::date('expiration_date',null,array('class'=>'form-control','required'=>'required'))}}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('document',__('Document'),array('class'=>'form-label')) }}
            {{Form::file('document',array('class'=>'form-control','required'=>'required'))}}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('license',__('License'),array('class'=>'form-label')) }}
            {{Form::file('license',array('class'=>'form-control','required'=>'required'))}}
        </div>
        <div class="form-group col-md-6">
            {{Form::label('reference',__('Reference'),array('class'=>'form-label')) }}
            {{Form::text('reference',null,array('class'=>'form-control','placeholder'=>__('Enter reference')))}}
        </div>
        <div class="form-group col-md-12">
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

