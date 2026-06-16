@component('mail::message')
# Your {{ $appName }} demo is ready

Hi {{ $user->name }},

Good news — your {{ $appName }} demo workspace is approved and ready to explore.
Set your password to log in:

@component('mail::button', ['url' => $url])
Set your password
@endcomponent

This is a single-use link and will expire after a while. If it lapses, use
**Forgot password** on the login page with this email
(**{{ $user->email }}**) and we'll send you a fresh one.

Thanks,<br>
The {{ $appName }} team
@endcomponent
