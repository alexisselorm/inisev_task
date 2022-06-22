@component('mail::message')
Hello {{ $email['name'] }}

A new post has been published

@component('mail::panel')
    Title: {{ $email['title'] }}<br><br>
{{ $email['body'] }}
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
