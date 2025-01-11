@extends('layouts.app')
@section('page-title')
    {{__('Reminder')}}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{route('dashboard')}}"><h1>{{__('Dashboard')}}</h1></a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">
                {{__('Reminder')}}
            </a>
        </li>
    </ul>
@endsection
@section('card-action-btn')
    @if(Gate::check('manage reminder'))
        <a class="btn btn-primary btn-sm ml-20 customModal" href="#" data-size="lg"
           data-url="{{ route('reminder.create') }}"
           data-title="{{__('Create Reminder')}}"> <i
                class="ti-plus mr-5"></i>
            {{__('Create Reminder')}}
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
                            <th>{{__('Date')}}</th>
                            <th>{{__('Title')}}</th>
                            <th>{{__('Type')}}</th>
                            <th>{{__('Vehicle')}}</th>
                            <th>{{__('Amount')}}</th>
                            <th>{{__('Notes')}}</th>
                            @if(Gate::check('edit reminder') || Gate::check('delete reminder'))
                                <th>{{__('Action')}}</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($reminders as $reminder)
                            <tr>
                                <td> {{ dateFormat($reminder->date) }} </td>
                                <td>{{ $reminder->title }} </td>
                                <td>{{ !empty($reminder->types)?$reminder->types->title:'-' }} </td>
                                <td>{{ !empty($reminder->vehicles)?$reminder->vehicles->name:'-' }} </td>
                                <td>{{ priceFormat($reminder->amount) }} </td>
                                <td>
                                    {{$reminder->notes}}
                                </td>
                                @if(Gate::check('edit reminder') || Gate::check('delete reminder') )
                                    <td>
                                        <div class="cart-action">
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['reminder.destroy', $reminder->id]]) !!}
                                            @if(!empty($reminder->receipt))
                                                <a  class="text-primary"  href="{{asset('/storage/upload/reminder/'.$reminder->receipt)}} "
                                                   target="_blank" data-bs-toggle="tooltip"
                                                    data-bs-original-title="{{__('Receipt')}}"> <i data-feather="file"></i> </a>
                                            @endif

                                            @can('edit reminder')
                                                <a class="text-success customModal" data-bs-toggle="tooltip"
                                                   data-bs-original-title="{{__('Edit')}}" href="#" data-size="lg"
                                                   data-url="{{ route('reminder.edit',$reminder->id) }}"
                                                   data-title="{{__('Edit reminder')}}"> <i data-feather="edit"></i></a>
                                            @endcan
                                            @can('delete reminder')
                                                <a class=" text-danger confirm_dialog" data-bs-toggle="tooltip"
                                                   data-bs-original-title="{{__('Detete')}}" href="#"> <i
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
@endsection
