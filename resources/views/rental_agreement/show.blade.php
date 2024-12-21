@extends('layouts.app')
@section('page-title')
    {{ rentalAgreementPrefix().$rentalAgreement->agreement_id }}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{route('dashboard')}}"><h1>{{__('Dashboard')}}</h1></a>
        </li>
        <li class="breadcrumb-item active">
            <a href="{{route('rental-agreement.index')}}">
                {{__('Rental Agreement')}}
            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">
                {{ rentalAgreementPrefix().$rentalAgreement->agreement_id }}
            </a>
        </li>
    </ul>
@endsection
@section('card-action-btn')
    <a class="btn btn-secondary print ml-5" href="javascript:void(0);"><i class="fa fa-print"></i> {{__('Print')}}</a>
@endsection
@section('content')
    <div id="invoice-print">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-body cdx-invoice">
                        <div id="cdx-invoice">
                            <div class="head-invoice">
                                <div class="codex-brand">
                                    <a class="codexbrand-logo" href="Javascript:void(0);">
                                        <img class="img-fluid"
                                             src="{{asset(Storage::url('upload/logo/')).'/'.(isset($settings['company_logo']) && !empty($settings['company_logo'])?$settings['company_logo']:'logo.png')}}"
                                             alt="invoice-logo">
                                    </a>
                                    <a class="codexdark-logo" href="Javascript:void(0);">
                                        <img class="img-fluid"
                                             src="{{asset(Storage::url('upload/logo/')).'/'.(isset($settings['company_logo']) && !empty($settings['company_logo'])?$settings['company_logo']:'logo.png')}}"
                                             alt="invoice-logo">
                                    </a>
                                </div>
                                <ul class="contact-list">
                                    <li>
                                        <div class="icon-wrap"><i class="fa fa-user"></i></div>
                                        {{$settings['company_name']}}
                                    </li>
                                    <li>
                                        <div class="icon-wrap"><i class="fa fa-phone"></i></div>
                                        {{$settings['company_phone']}}
                                    </li>
                                    <li>
                                        <div class="icon-wrap"><i class="fa fa-envelope"></i></div>
                                        {{$settings['company_email']}}
                                    </li>

                                </ul>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <h5 class="text-primary mb-10">
                                       {{__('Agreement')}} : </h5>

                                    <div class="col-md-4 col-lg-4 col-sm-4">
                                        <div class="detail-group">
                                            <h6>{{__('Agreement ID')}}</h6>
                                            <p class="mb-20">{{rentalAgreementPrefix().$rentalAgreement->agreement_id}} </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-lg-4 col-sm-4">
                                        <div class="detail-group">
                                            <h6>{{__('Agreement Date')}}</h6>
                                            <p class="mb-20"> {{dateFormat($rentalAgreement->date)}} </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-lg-4 col-sm-4">
                                        <div class="detail-group">
                                            <h6>{{__('Rental Start Date')}}</h6>
                                            <p class="mb-20">{{dateFormat($rentalAgreement->rental_start_date)}} </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-lg-4 col-sm-4">
                                        <div class="detail-group">
                                            <h6>{{__('Rental End Date')}}</h6>
                                            <p class="mb-20">{{dateFormat($rentalAgreement->rental_end_date)}} </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-lg-4 col-sm-4">
                                        <div class="detail-group">
                                            <h6>{{__('Rental Duration')}}</h6>
                                            <p class="mb-20">{{$rentalAgreement->rental_duration.__(' Days')}} </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-lg-4 col-sm-4">
                                        <div class="detail-group">
                                            <h6>{{__('Status')}}</h6>
                                            <p class="mb-20">
                                                @if($rentalAgreement->status=='draft')
                                                    <span class="badge badge-info">{{\App\Models\RentalAgreement::$status[$rentalAgreement->status]}}</span>
                                                @elseif($rentalAgreement->status=='pending')
                                                    <span class="badge badge-warning">{{\App\Models\RentalAgreement::$status[$rentalAgreement->status]}}</span>
                                                @elseif($rentalAgreement->status=='confirmed' || $rentalAgreement->status=='active')
                                                    <span class="badge badge-success">{{\App\Models\RentalAgreement::$status[$rentalAgreement->status]}}</span>
                                                @else
                                                    <span class="badge badge-danger">{{\App\Models\RentalAgreement::$status[$rentalAgreement->status]}}</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <h5 class="text-primary mb-10">
                                       {{__('Driver')}} : </h5>
                                    <div class="col-md-4 col-lg-4 col-sm-4">
                                        <div class="detail-group">
                                            <h6>{{__('Name')}}</h6>
                                            <p class="mb-20"> {{!empty($rentalAgreement->drivers)?$rentalAgreement->drivers->name:''}} </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-lg-4 col-sm-4">
                                        <div class="detail-group">
                                            <h6>{{__('Email')}}</h6>
                                            <p class="mb-20">{{!empty($rentalAgreement->drivers)?$rentalAgreement->drivers->email:''}} </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-lg-4 col-sm-4">
                                        <div class="detail-group">
                                            <h6>{{__('Phone Number')}}</h6>
                                            <p class="mb-20">{{!empty($rentalAgreement->drivers)?$rentalAgreement->drivers->phone_number:''}}</p>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <h5 class="text-primary mb-10">
                                       {{__('Vehicle')}} : </h5>
                                    <div class="col-md-4 col-lg-4 col-sm-4">
                                        <div class="detail-group">
                                            <h6>{{__('Vehicle')}}</h6>
                                            <p class="mb-20"> {{!empty($rentalAgreement->vehicles)?$rentalAgreement->vehicles->name:''}} </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-lg-4 col-sm-4">
                                        <div class="detail-group">
                                            <h6>{{__('Model')}}</h6>
                                            <p class="mb-20">{{!empty($rentalAgreement->vehicles)?$rentalAgreement->vehicles->model:''}} </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-lg-4 col-sm-4">
                                        <div class="detail-group">
                                            <h6>{{__('License Plate')}}</h6>
                                            <p class="mb-20">{{!empty($rentalAgreement->vehicles)?$rentalAgreement->vehicles->license_plate:''}}</p>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <h5 class="text-primary mb-10">
                                       {{__('Terms & Conditions')}} : </h5>
                                    <div class="col-md-12 col-lg-12 col-sm-4">
                                        <p>
                                            {{$rentalAgreement->terms_condition}}
                                        </p>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <h5 class="text-primary mb-10">
                                       {{__('Description')}} : </h5>
                                    <div class="col-md-12 col-lg-12 col-sm-4">
                                        <p>
                                            {{$rentalAgreement->description}}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 text-end mt-20">
                                <h5>{{__('Signature')}}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
@push('script-page')
    <script>
        $(document).on('click', '.print', function () {
            $('.action').addClass('d-none');
            var printContents = document.getElementById('invoice-print').innerHTML;
            var originalContents = document.body.innerHTML;

            document.body.innerHTML = printContents;

            window.print();

            document.body.innerHTML = originalContents;
            $('.action').removeClass('d-none');
        });
    </script>
@endpush
