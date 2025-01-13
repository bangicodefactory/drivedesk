@extends('layouts.app')
@section('page-title')
    {{ __('Reminder') }}
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
                {{ __('Reminder') }}
            </a>
        </li>
    </ul>
@endsection
@section('card-action-btn')
    @if (Gate::check('manage reminder'))
        <a class="btn btn-primary btn-sm ml-20 customModal" href="#" data-size="lg"
            data-url="{{ route('reminder.create') }}" data-title="{{ __('Create Reminder') }}"> <i class="ti-plus mr-5"></i>
            {{ __('Create Reminder') }}
        </a>
    @endif
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table class="display dataTable cell-border datatbl-advance">
                        <thead>
                            <tr>
                                <th>{{ __('Next appointment date') }}</th>
                                <th>{{ __('Title') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Vehicle') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Notes') }}</th>
                                @if (Gate::check('edit reminder') || Gate::check('delete reminder'))
                                    <th>{{ __('Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reminders as $reminder)
                                <tr>
                                    <td>
                                        <a href="#" 
                                        class="customModal" 
                                        data-bs-toggle="tooltip"
                                        data-bs-original-title="{{__('View days remaining')}}"
                                        data-size="sm"
                                        data-url="{{ route('reminder.days-remaining', $reminder->id) }}"
                                        data-title="{{__('Jours avant le rappel')}}">
                                         {{ dateFormat($reminder->reminder_date) }}
                                     </a>
                                    </td>
                                    <td>{{ $reminder->name }} </td>
                                    <td>{{ !empty($reminder->reminderType) ? $reminder->reminderType->type : '-' }} </td>
                                    <td>{{ !empty($reminder->vehicles) ? $reminder->vehicles->name : '-' }} </td>
                                    <td>
                                        {{-- {{ $reminder->status }}  --}}
                                        @if ($reminder->status == 'pending')
                                            <span class="badge bg-primary text-white">{{ __('Pending') }}</span>
                                        @elseif($reminder->status == 'upcoming')
                                            <span class="badge bg-secondary text-white">{{ __('Upcoming') }}</span>
                                        @elseif($reminder->status == 'urgent')
                                            <span class="badge bg-warning text-dark">{{ __('Urgent') }}</span>
                                        @elseif($reminder->status == 'overdue')
                                            <span class="badge bg-danger text-white">{{ __('Overdue') }}</span>
                                        @endif

                                    </td>
                                    <td>
                                        {{ $reminder->note }}
                                    </td>
                                    @if (Gate::check('edit reminder') || Gate::check('delete reminder'))
                                        <td>
                                            <div class="cart-action">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['reminder.destroy', $reminder->id]]) !!}

                                                @can('edit reminder')
                                                    <a class="text-success customModal" data-bs-toggle="tooltip"
                                                        data-bs-original-title="{{ __('Edit') }}" href="#"
                                                        data-size="lg" data-url="{{ route('reminder.edit', $reminder->id) }}"
                                                        data-title="{{ __('Edit reminder') }}"> <i data-feather="edit"></i></a>
                                                @endcan
                                                @can('delete reminder')
                                                    <a class=" text-danger confirm_dialog" data-bs-toggle="tooltip"
                                                        data-bs-original-title="{{ __('Detete') }}" href="#"> <i
                                                            data-feather="trash-2"></i></a>
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
    {{-- @include('reminder._date_modal') --}}
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
