@extends('layouts.app')
@section('page-title')
    {{__('Booking')}}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{route('dashboard')}}"><h1>{{__('Dashboard')}}</h1></a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">
                {{__('Booking')}}
            </a>
        </li>
    </ul>
@endsection
@section('card-action-btn')
    @if(Gate::check('manage vehicle'))
        <a class="btn btn-primary btn-sm ml-20" href="{{ route('booking.create') }}"> <i
                class="ti-plus mr-5"></i>
            {{__('Create Booking')}}
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
                            <th>{{__('ID')}}</th>
                            <th>{{__('Driver')}}</th>
                            <th>{{__('Vehicle')}}</th>
                            <th>{{__('Duration')}}</th>
                            <th data-column="status">{{__('Status')}}</th>
                            <th data-column="payment_status">{{__('Payment Status')}}</th>
                            @if(Gate::check('edit booking') || Gate::check('delete booking') || Gate::check('show booking'))
                                <th>{{__('Action')}}</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($bookings as $booking)
                            <tr>
                                <td hidden>{{ $booking->id }}</td>
                                <td>{{ bookingPrefix().$booking->booking_id }}</td>
                                <td>{{ !empty($booking->drivers)?$booking->drivers->name:'-' }}</td>
                                <td>{{ !empty($booking->vehicleDetails())?$booking->vehicleDetails()->name:'-' }} - {{ !empty($booking->vehicleDetails())?$booking->vehicleDetails()->license_plate:'-' }}</td>
                                <td>
                                    {{ dateFormat($booking->start_date) .' / '. timeFormat($booking->start_time)}} <br>
                                    {{ dateFormat($booking->end_date) .' / '. timeFormat($booking->end_time)}}
                                </td>
                                <td data-status="{{ $booking->status }}">
                                    @if($booking->status=='yet_to_start')
                                        <span class="badge badge-primary">{{\App\Models\Booking::$status[$booking->status]}}</span>
                                    @elseif($booking->status=='completed')
                                        <span class="badge badge-success">{{\App\Models\Booking::$status[$booking->status]}}</span>
                                    @elseif($booking->status=='on_going')
                                        <span class="badge badge-warning">{{\App\Models\Booking::$status[$booking->status]}}</span>
                                    @elseif($booking->status=='cancelled')
                                        <span class="badge badge-danger">{{\App\Models\Booking::$status[$booking->status]}}</span>
                                    @endif
                                </td>
                                <td data-payment-status="{{ $booking->payment_status }}">
                                    @if($booking->payment_status=='paye')
                                        <span class="badge badge-success">{{\App\Models\Booking::$paymentStatus[$booking->payment_status]}}</span>
                                    @elseif($booking->payment_status=='impaye')
                                        <span class="badge badge-danger">{{\App\Models\Booking::$paymentStatus[$booking->payment_status]}}</span>
                                    @elseif($booking->payment_status=='partiellement_paye')
                                        <span class="badge badge-warning">{{\App\Models\Booking::$paymentStatus[$booking->payment_status]}}</span>
                                    @endif
                                </td>
                                @if(Gate::check('edit booking') || Gate::check('delete booking') || Gate::check('show booking'))
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
                                            @can('delete booking')
                                                <a class="text-danger confirm_dialog" data-bs-toggle="tooltip"
                                                   data-bs-original-title="{{__('Delete')}}" href="#"> 
                                                    <i data-feather="trash-2"></i>
                                                </a>
                                            @endcan
                                            {!! Form::close() !!}
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Destroy existing DataTable if it exists
    if ($.fn.DataTable.isDataTable('#bookingTable')) {
        $('#bookingTable').DataTable().destroy();
    }
    
    // Reinitialize
    $('#bookingTable').DataTable({
        columnDefs: [
            // Your column definitions
        ],
        order: [[0, 'desc']]
    });
});
</script>
@endpush