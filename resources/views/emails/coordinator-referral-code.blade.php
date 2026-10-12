<x-mail::message>
# Welcome, {{ $coordinator->name }}

You have been set up as the **{{ $coordinator->role->label() }}** for **{{ $coordinator->localGovernment?->name ?? 'your area' }}** on the Lagos Grassroots Alliance platform.

## Your referral code

Agents you recruit sign up with this code, and every agent who uses it is credited to you:

<x-mail::panel>
**{{ $coordinator->referral_code }}**
</x-mail::panel>

The easiest way is to share this link — it fills the code in for them:

<x-mail::button :url="$signupUrl">
Agent sign-up link
</x-mail::button>

{{ $signupUrl }}

## Access your dashboard

1. Set your password: open {{ $setPasswordUrl }} and enter **{{ $coordinator->email }}**. We will email you a link.
2. Log in at {{ $loginUrl }}.
3. Set up two-factor authentication when prompted (required for coordinators).

Your dashboard shows your code at any time, along with the wards, agents and members in your area.

One Lagos, Many Voices.<br>
{{ config('app.name') }}
</x-mail::message>
