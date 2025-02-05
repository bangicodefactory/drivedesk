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
                    <table class="display dataTable cell-border datatbl-advance">
                        <thead>
                            <tr>
                                <th>{{ __('Period') }}</th>
                                <th>{{ __('Total amout') }}</th>
                                <th>{{ __('TVA amout') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Generate date') }}</th> 
                                @if (Gate::check('edit reminder') || Gate::check('delete reminder'))
                                    <th>{{ __('Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tvaFiles as $tvaFile)
                                <tr>
                                    <td>{{ Carbon\Carbon::create()->month($tvaFile->month)->format('F') }} {{ $tvaFile->year }}</td>
                                    <td>{{ number_format($tvaFile->total_amount, 2) }} </td>
                                    <td>{{ number_format($tvaFile->tva_amount, 2) }} </td>
                                    <td>{{ $tvaFile->status }}</td>
                                    <td>
                                        {{ $tvaFile->generated_date->format('d/m/Y') }}
                                    </td>
                                    @if (Gate::check('edit reminder') || Gate::check('delete reminder'))
                                        <td>
                                            <div class="cart-action">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['tva.destroy', $tvaFile->id]]) !!}

                                                @can('edit reminder')
                                                    <a class="text-success customModal" data-bs-toggle="tooltip"
                                                        data-bs-original-title="{{ __('Edit') }}" href="#"
                                                        data-size="lg" data-url="{{ route('tva.edit', $tvaFile->id) }}"
                                                        data-title="{{ __('Edit tva') }}"> <i data-feather="edit"></i></a>
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
