@extends('layouts.auth')
@php
    $settings=settings();
@endphp
@section('tab-title')
    {{__('Register')}}
@endsection
@push('script-page')
    @if ($settings['google_recaptcha'] == 'on')
        {!! NoCaptcha::renderJs() !!}
    @endif
@endpush
@section('content')
    <div class="codex-authbox">
        <div class="auth-header">
            <div class="codex-brand">
                <a href="#">
                    <img class="img-fluid light-logo" src="{{asset(Storage::url('upload/logo/')).'/logo.png'}}" alt="">
                    <img class="img-fluid dark-logo" src="{{asset(Storage::url('upload/logo/')).'/logo.png'}}" alt="">
                </a>
            </div>
            <h3>{{__('Create your account')}}</h3>
        </div>
        {{Form::open(array('route'=>'register','method'=>'post','id'=>'loginForm'))}}
        @if (session('error'))
            <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
        @endif
        @if (session('success'))
            <div class="alert alert-success" role="alert">{{ session('success') }}</div>
        @endif
        <div class="form-group ">
            {{Form::label('name',__('Name'),array('class'=>'form-label'))}}
            {{Form::text('name',null,array('class'=>'form-control','placeholder'=>__('Enter Name')))}}
            @error('name')
            <span class="invalid-name text-danger" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group ">
            {{Form::label('email',__('Email'),array('class'=>'form-label'))}}
            {{Form::text('email',null,array('class'=>'form-control','placeholder'=>__('Enter Email')))}}
            @error('email')
            <span class="invalid-email text-danger" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="form-group">
            {{Form::label('password',__('Password'),array('class'=>'form-label'))}}
            <div class="input-group group-input">
                <input class="form-control showhide-password" type="password" name="password" id="Password"
                       placeholder="{{__('Enter Password')}}" required="">
                <span class="input-group-text toggle-show fa fa-eye"></span>
            </div>
            @error('password')
            <span class="invalid-password text-danger" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            {{Form::label('password_confirmation',__('Password Confirmation'),array('class'=>'form-label'))}}
            <div class="input-group group-input">
                <input class="form-control showhide-password" type="password" name="password_confirmation" id="password_confirmation"
                       placeholder="{{__('Enter Confirm Password')}}" required="">
                <span class="input-group-text toggle-show fa fa-eye"></span>
            </div>
            @error('password_confirmation')
            <span class="invalid-password_confirmation text-danger" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group ">
            {{Form::label('company_name',__('Company Name'),array('class'=>'form-label'))}}
            {{Form::text('company_name',null,array('class'=>'form-control','placeholder'=>__('Enter Company Name')))}}
            @error('company_name')
            <span class="invalid-company_name text-danger" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group ">
            {{Form::label('city',__('City'),array('class'=>'form-label'))}}
            {{Form::text('city',null,array('class'=>'form-control','placeholder'=>__('Enter City')))}}
            @error('city')
            <span class="invalid-city text-danger" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group mb-0">
            <div class="auth-remember">
                <div class="form-check custom-chek">
                    <input class="form-check-input" id="agree" type="checkbox" value="" required="">
                    <label class="form-check-label" for="agree">{{__('I Agree Terms and conditions')}}</label>
                </div>
            </div>
        </div>
        @if ($settings['google_recaptcha'] == 'on')
            <div class="form-group">
                <label for="email" class="form-label"></label>
                {!! NoCaptcha::display() !!}
                @error('g-recaptcha-response')
                <span class="small text-danger" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            @if ($errors->has('g-recaptcha-response'))
                <span class="help-block">
                    <strong>{{ $errors->first('g-recaptcha-response') }}</strong>
                </span>
            @endif
        @endif
        <div class="form-group">
            <button class="btn btn-primary" type="submit"><i class="fa fa-paper-plane"></i> {{__('Register')}}</button>
        </div>
        {{Form::close()}}
        <div class="auth-footer">
            <h6 class="text-center">{{__('Already have an account?')}} <a class="text-primary" href="{{ route('login') }}">{{__('Login in here')}}</a></h6>
        </div>
    </div>
@endsection

