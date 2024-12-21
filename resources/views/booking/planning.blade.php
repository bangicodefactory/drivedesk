@extends('layouts.app')
@section('page-title')
    {{__('Planning')}}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{route('dashboard')}}"><h1>{{__('Dashboard')}}</h1></a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">
                {{__('Planning')}}
            </a>
        </li>
    </ul>
@endsection
@push('css-page')

@endpush
@push('script-page')
    <script src="{{ asset('js/index.global.js') }}"></script>
    <script>
        var bookingData={!! json_encode($bookingData) !!};
        var vehicleData={!! json_encode($vehicleData) !!};
        document.addEventListener('DOMContentLoaded', function () {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                now: new Date(),
                editable: false,
                aspectRatio: 1.8,
                scrollTime: '00:00',
                headerToolbar: {
                    left: 'today prev,next',
                    center: 'title',
                    right: 'resourceTimelineDay,resourceTimelineWeek,resourceTimelineMonth,resourceTimelineYear'
                },
                initialView: 'resourceTimelineMonth',
                views: {

                },
                navLinks: true,
                resourceAreaWidth: '25%',
                resourceAreaHeaderContent: 'Vehicles',
                resources: vehicleData,
                events: bookingData,
                eventContent: function(arg) {
                    let customEventContent = document.createElement('div');
                    customEventContent.innerHTML = `<div class="fc-event-title">${arg.event.title}</div>`;
                    return { domNodes: [customEventContent] };
                },

            });

            calendar.render();
        });
        document.addEventListener('DOMContentLoaded', function () {
            var todayElement = document.querySelector('.fc-day-today');
            if (todayElement) {
                var container = todayElement.closest('.fc-scroller');
                var scrollLeft = todayElement.offsetLeft - (container.offsetWidth / 2) + (todayElement.offsetWidth / 2);
                container.scrollLeft = scrollLeft;
            }
        });
    </script>
@endpush
@section('page-class')
    codex-calendar
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class=" col-xxl-12">
                            <div id='calendar'></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
