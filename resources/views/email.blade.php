@component('mail::message')
# New Contact Form Submission

You have received a new contact form submission.

**Name:** {{ $name }}  
**Email:** {{ $email }}  
**Subject:** {{ $subject }}

**Message:**  
{{ $message }}

@component('mail::button', ['url' => config('app.url')])
View Admin Panel
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent