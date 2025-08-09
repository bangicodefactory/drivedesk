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
                    // Month-only tweak: use hour-level slots so two bookings on the same day
                    // (with different times) can render on a single vehicle row.
                    resourceTimelineMonth: {
                        // Show hour-level slots within the month so events on the same day
                        // (but at different times) can sit on one line.
                        slotDuration: '01:00',
                        // Keep major labels at the day level for readability
                        slotLabelInterval: { days: 1 },
                        // Optional: prevent columns from becoming too narrow on small screens
                        slotMinWidth: 40,
                        // Keep a single vertical lane per vehicle in Month view
                        // Extra overlapping bookings are accessible via "+N more" popover.
                        eventMaxStack: 1
                    }
                },
                navLinks: true,
                resourceAreaWidth: '25%',
                resourceAreaHeaderContent: 'Vehicles',
                resources: vehicleData,
                events: bookingData,
                // Be explicit: allow overlapping events to render side-by-side on a row
                eventOverlap: true,
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
