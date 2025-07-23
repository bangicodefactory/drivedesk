@extends('layouts.app')
@section('page-title')
    {{__('Planning')}}
@endsection
@section('breadcrumb')
<li class="breadc@section('page-css')
    <style>
        /* Force events to stay in their assigned resource rows */
        .fc-resource-7 .fc-event:not(.resource-7) {
            display: none !important;
        }
        .fc-resource-8 .fc-event:not(.resource-8) {
            display: none !important;
        }
        .fc-resource-9 .fc-event:not(.resource-9) {
            display: none !important;
        }
        
        /* Ensure events only show in correct resource */
        .resource-7 {
            --resource-id: 7;
        }
        .resource-8 {
            --resource-id: 8;
        }
        .resource-9 {
            --resource-id: 9;
        }
        
        /* Alternative: Force events to display only in matching resource rows */
        .fc-timeline-event {
            position: relative;
        }
    </style>
@endsectionb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
<li class="breadcrumb-item" aria-current="page">{{ __('Planning') }}</li>
@endsection
@push('css-page')

@endpush
@push('script-page')
    <script src="{{ asset('js/index.global.js') }}"></script>
    <script>
        var bookingData={!! json_encode($bookingData) !!};
        var vehicleData={!! json_encode($vehicleData) !!};
        
        // Debug: Check the data mapping
        console.log('=== DEBUG INFO ===');
        console.log('Vehicle Data:', vehicleData);
        console.log('Booking Data:', bookingData);
        
        // Check for mismatches
        bookingData.forEach(function(booking) {
            var vehicleExists = vehicleData.find(function(vehicle) {
                return vehicle.id === booking.resourceId;
            });
            console.log('Booking ' + booking.title + ' -> Resource ID: ' + booking.resourceId + ' -> Vehicle exists: ' + (vehicleExists ? 'YES' : 'NO'));
        });
        
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
                    resourceTimelineMonth: {
                        eventOverlap: false,
                        slotEventOverlap: false,
                        eventResourceEditable: false,
                        resourceAreaWidth: '25%',
                        slotMinWidth: 50,
                        eventMinHeight: 15,
                        eventDisplay: 'block',
                        eventMaxStack: 10
                    }
                },
                navLinks: true,
                resourceAreaWidth: '25%',
                resourceAreaHeaderContent: 'Vehicles',
                resources: vehicleData,
                events: bookingData,
                eventOverlap: false,
                slotEventOverlap: false,
                eventResourceEditable: false,
                resourceAreaHeaderContent: 'Vehicles',
                resourceAreaWidth: '200px',
                
                // Custom event positioning to force resource compliance
                eventClassNames: function(arg) {
                    return ['resource-' + arg.event._def.resourceIds[0]];
                },
                
                // Prevent drag and drop between resources
                eventDragStart: function(info) {
                    info.jsEvent.preventDefault();
                },
                
                // Prevent resource switching in month view
                views: {
                    resourceTimelineMonth: {
                        eventOverlap: false,
                        slotEventOverlap: false,
                        eventMaxStack: 10,
                        resourceAreaWidth: '200px'
                    }
                },
                eventContent: function(arg) {
                    let customEventContent = document.createElement('div');
                    customEventContent.innerHTML = `<div class="fc-event-title">${arg.event.title}</div>`;
                    return { domNodes: [customEventContent] };
                },
                eventDidMount: function(info) {
                    // Debug event and resource assignment
                    console.log('Event:', info.event.title);
                    console.log('Assigned resourceId:', info.event._def.resourceIds);
                    console.log('Event object:', info.event);
                    
                    // Find the correct resource row for this event
                    const resourceId = info.event._def.resourceIds[0];
                    const correctResourceRow = document.querySelector(`[data-resource-id="${resourceId}"]`);
                    
                    if (correctResourceRow) {
                        console.log('Found correct resource row for', info.event.title, '-> Resource', resourceId);
                        
                        // If event is not in the correct row, move it
                        const currentRow = info.el.closest('[data-resource-id]');
                        if (currentRow && currentRow.dataset.resourceId !== resourceId) {
                            console.log('Moving event', info.event.title, 'from resource', currentRow.dataset.resourceId, 'to', resourceId);
                            
                            // Try to move the event to the correct resource container
                            const correctContainer = correctResourceRow.querySelector('.fc-timeline-events') || 
                                                   correctResourceRow.querySelector('.fc-event-container') ||
                                                   correctResourceRow;
                            
                            if (correctContainer) {
                                correctContainer.appendChild(info.el);
                            }
                        }
                    } else {
                        console.log('Could not find resource row for', resourceId);
                    }
                    
                    console.log('Rendered on resource:', info.el.closest('[data-resource-id]')?.dataset?.resourceId || 'unknown');
                },
                
                // Force resource assignment for month view
                viewDidMount: function(info) {
                    console.log('View mounted:', info.view.type);
                    if (info.view.type === 'resourceTimelineMonth') {
                        // Apply specific settings for month view
                        console.log('Month view settings applied');
                    }
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

@endsection
@section('content')
    <div class="row codex-calendar">
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
