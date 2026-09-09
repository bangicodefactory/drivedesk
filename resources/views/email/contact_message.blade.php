@component('mail::message')
# {{ __('New message from the website') }}

@component('mail::panel')
**{{ $data['name'] }}**
@endcomponent

- **{{ __('Email') }}:** [{{ $data['email'] }}](mailto:{{ $data['email'] }})
- **{{ __('Phone') }}:** {{ $data['phone'] ?: '—' }}
@if(!empty($data['reference']))
- **{{ __('Booking reference') }}:** {{ $data['reference'] }}
@endif

**{{ __('Message') }}**

{{ $data['message'] }}

@component('mail::button', ['url' => 'mailto:' . $data['email']])
{{ __('Reply to :name', ['name' => $data['name']]) }}
@endcomponent
@endcomponent
