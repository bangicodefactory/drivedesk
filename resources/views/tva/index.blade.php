@extends('layouts.app')
@section('page-title')
    {{ __('TVA') }}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">
                <h1>{{ __('Dashboard') }}</h1>
            </a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">
                {{ __('TVA') }}
            </a>
        </li>
    </ul>
@endsection
@section('card-action-btn')
    @if (Gate::check('manage reminder'))
        <a class="btn btn-primary btn-sm ml-20 customModal" href="#" data-size="lg"
            data-url="{{ route('tva.create') }}" data-title="{{ __('Create TVA') }}"> <i class="ti-plus mr-5"></i>
            {{ __('Create TVA') }}
        </a>
    @endif
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table class="display dataTable cell-border datatbl-advance" id="bookingTable">
                        <thead>
                        <tr>
                            <th hidden>id</th>
                            <th>{{__('Facture N°')}}</th>
                            <th>{{__('Designation')}}</th>
                            <th>{{__('Date')}}</th>
                            <th>{{__('TTC')}}</th>
                            @if(Gate::check('edit booking') || Gate::check('delete booking') || Gate::check('show booking'))
                                <th>{{__('Action')}}</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($tvas as $tva)
                            <tr>
                                <td hidden>{{ $tva->id }}</td>
                                <td>{{ bookingPrefix().$tva->facture_number }}</td>
                                <td>{{ !empty($tva->designation)?$tva->designation:'-' }}</td>

                                <td>
                                    {{ dateFormat($tva->created_at) }}
                                </td>
                                <td>
                                    {{ $tva->montant_ttc }} Dh
                                </td>
                                
                                {{-- @if(Gate::check('edit booking') || Gate::check('delete booking') || Gate::check('show booking'))
                                    <td>
                                        <div class="cart-action">
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['booking.destroy', $booking->id]]) !!}
                                            @can('show booking')
                                                <a class="text-warning"
                                                   data-bs-toggle="tooltip"
                                                   data-bs-original-title="{{__('Details')}}"
                                                   href="{{ route('booking.show',\Illuminate\Support\Facades\Crypt::encrypt($booking->id)) }}"
                                                > <i data-feather="eye"></i></a>
                                            @endcan
                                            @can('edit booking')
                                                <a class="text-success" data-bs-toggle="tooltip"
                                                   data-bs-original-title="{{__('Edit')}}"
                                                   href="{{ route('booking.edit',\Illuminate\Support\Facades\Crypt::encrypt($booking->id)) }}">
                                                    <i data-feather="edit"></i></a>
                                            @endcan
                                            
                                            {!! Form::close() !!}
                                        </div>
                                    </td>
                                @endif --}}
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
{{-- @push('scripts')
    <script>
        $(document).ready(function() {
            $('.reminder-date').on('click', function(e) {
                e.preventDefault();

                var reminderDate = $(this).data('date');
                var today = new Date();
                var reminder = new Date(reminderDate);
                var diffTime = reminder.getTime() - today.getTime();
                var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                var message = '';
                if (diffDays > 0) {
                    message = '{{ __('There are') }} ' + diffDays +
                        ' {{ __('days remaining until this reminder.') }}';
                } else if (diffDays < 0) {
                    message = '{{ __('This reminder is overdue by') }} ' + Math.abs(diffDays) +
                        ' {{ __('days.') }}';
                } else {
                    message = '{{ __('This reminder is due today!') }}';
                }

                $('#daysRemaining').html(message);
                $('#dateModal').modal('show');
            });
        });
    </script>
@endpush --}}
