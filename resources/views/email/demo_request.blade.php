@component('mail::message')
# New demo request

A visitor requested a DriveDesk demo from the marketing site.

@component('mail::panel')
**{{ $data['name'] }}** — {{ $data['company'] }}
@endcomponent

- **Email:** [{{ $data['email'] }}](mailto:{{ $data['email'] }})
- **Phone:** {{ $data['phone'] ?: '—' }}

@if(!empty($data['message']))
**Message**

{{ $data['message'] }}
@endif

@component('mail::button', ['url' => 'mailto:' . $data['email']])
Reply to {{ $data['name'] }}
@endcomponent

Thanks,<br>
DriveDesk
@endcomponent
