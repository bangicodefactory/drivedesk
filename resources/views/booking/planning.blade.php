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
    <style>
        /* The lane that holds events should be the positioning context */
        .fc-timeline .fc-timeline-lane .fc-timeline-events {
            position: relative;
            overflow: visible; /* let absolute children spill over */
        }
        /* When a row has hidden events, give it extra vertical space */
        .fc-timeline .fc-timeline-lane.fc-has-more {
            min-height: 48px; /* taller row for readability */
        }
        .fc-timeline .fc-timeline-lane.fc-has-more .fc-timeline-events {
            padding-top: 18px; /* create a gap at the top for the +N pill */
        }
        /* Make the "+N more" appear as a small inline pill overlayed in the same car row */
    .fc-timeline .fc-more-link.fc-more-inline {
            position: absolute; /* overlay, not consuming row height */
            top: 2px; /* stick to the very top of the row */
            transform: none;
            height: 18px;
            line-height: 18px;
            padding: 0 6px;
            border-radius: 10px;
            font-size: 11px;
            color: #fff;
            background: rgba(33, 150, 243, 0.7);
            z-index: 10002; /* above event elements */
            pointer-events: auto; /* clickable */
            display: inline-block;
            white-space: nowrap;
            cursor: pointer;
        }
        /* Ensure the event area can host the absolute element without clipping */
        .fc-timeline .fc-event-area, .fc-timeline .fc-scroller-harness, .fc-timeline .fc-timeline-body {
            overflow: visible;
        }
    </style>
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
                        slotDuration: '02:00',
                        // Keep major labels at the day level for readability
                        slotLabelInterval: { days: 1 },
                        // Optional: prevent columns from becoming too narrow on small screens
                        slotMinWidth: 20,
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
                // Render the more-link as an inline overlay within the same row
                moreLinkClassNames: ['fc-more-inline'],
                // Ensure clicking the +N pill opens the popover
                moreLinkClick: 'popover',
                moreLinkDidMount: function(arg) {
                    // Defensive in case class injection is blocked by theme
                    arg.el.classList.add('fc-more-inline');
                    // Keep FC's computed horizontal position; only force vertical/top
                    arg.el.style.position = 'absolute';
                    arg.el.style.top = '2px';
                    arg.el.style.transform = 'none';
                    arg.el.style.zIndex = '10002';
                    arg.el.style.pointerEvents = 'auto';

                    // Find lane and event container, then add space inline
                    var eventsWrap = arg.el.closest('.fc-timeline-events');
                    var lane = eventsWrap ? eventsWrap.closest('.fc-timeline-lane') : arg.el.closest('.fc-timeline-lane');
                    if (lane) {
                        lane.classList.add('fc-has-more');
                        try { lane.style.minHeight = '56px'; } catch (e) {}
                        // Ensure lane is positioning context
                        if (getComputedStyle(lane).position === 'static') {
                            try { lane.style.position = 'relative'; } catch (e) {}
                        }
                    }

                    // Move the pill to the lane so it sits above events and keep horizontal position
                    try {
                        var left = arg.el.offsetLeft;
                        // Append after events for highest paint order
                        if (lane && arg.el.parentElement !== lane) {
                            lane.appendChild(arg.el);
                        }
                        arg.el.style.left = left + 'px';
                    } catch (e) {}

                    if (eventsWrap) {
                        try { eventsWrap.style.position = 'relative'; } catch (e) {}
                        try { eventsWrap.style.overflow = 'visible'; } catch (e) {}
                        try { eventsWrap.style.paddingTop = '18px'; } catch (e) {}
                    }
                },
                moreLinkContent: function(arg) {
                    // Compact "+N" label
                    return { html: '+' + arg.num + '' };
                },
                // Always navigate via event.url (works for items in "+N more" popovers too)
                eventClick: function(info) {
                    // Ignore if the click originated from the more-link
                    if (info.jsEvent && info.jsEvent.target && typeof info.jsEvent.target.closest === 'function') {
                        if (info.jsEvent.target.closest('.fc-more-link')) {
                            return;
                        }
                    }
                    const url = info.event.url || (info.event.extendedProps && info.event.extendedProps.url);
                    if (url) {
                        info.jsEvent.preventDefault();
                        window.location.href = url;
                    }
                },
                // Make events semi-transparent to better see overlaps
                eventDidMount: function(info) {
                    const el = info.el;
                    // Background with alpha
                    el.style.backgroundColor = 'rgba(33, 150, 243, 0.35)'; // blue with transparency
                    // Border more opaque for edges
                    el.style.borderColor = 'rgba(33, 150, 243, 0.85)';
                    // Keep readable text
                    el.style.color = '#fff';
                },
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
