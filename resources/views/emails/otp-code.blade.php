<x-mail::message>
# Verify your account

Hello {{ $user->name }},

Your Lagos Grassroots Alliance verification code is:

**{{ $code }}**

This code expires in 10 minutes.

If you did not request this code, you can ignore this email.

One Lagos, Many Voices.<br>
{{ config('app.name') }}
</x-mail::message>
