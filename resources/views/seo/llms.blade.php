# {{ config('app.name') }}

> {{ config('app.name') }} (LGA) is a non-partisan, people-led grassroots movement in Lagos State, Nigeria. Its message is "{{ config('seo.tagline') }}": everyday Lagosians across all 20 Local Government Areas coming together as one voice to protect votes, strengthen institutions and make sure communities are heard.

Key facts:

- **What it is:** a citizens' movement, not a political party. In its own words: "not a party and not a podium — it's the people."
- **Who it is for:** every Lagosian — traders and students, drivers and doctors, from Ikoyi to Ikorodu and Epe to Badagry. Everyone is welcome.
- **Where it operates:** all 20 Local Government Areas of Lagos State, organised down to wards and polling units.
- **What members stand for:**
  - One voice, many people: standing together as Lagosians.
  - Protect our votes: making sure votes are counted and respected.
  - Stronger institutions: a Lagos that is fair, accountable and built to last.
  - Make sure we're heard: turning individual concerns into a collective voice leaders can't ignore.
- **How it is organised:** community members are brought in by agents; agents are recruited by ward and LGA coordinators. Each member is linked to their LGA, ward and polling unit.
- **How to join:** sign up free at {{ route('join') }} using an agent's referral code. Registration takes under two minutes.
- **How to become an agent or coordinator:** apply through the Coordinators Corner at {{ route('become-agent') }} with a coordinator's referral code. Existing members can also apply to become agents from their dashboard.
- **Contact:** {{ config('mail.from.address') }}

## Main pages

- [Home]({{ route('home') }}): who the Alliance is and why it exists
- [Join the Alliance]({{ route('join') }}): membership sign-up for community members
- [Coordinators Corner]({{ route('become-agent') }}): sign-up for agents and coordinators
- [News]({{ route('posts.index') }}): official updates, events and galleries

## News
@forelse ($posts as $post)
- [{{ $post->title }}]({{ route('posts.show', $post) }}) ({{ $post->published_at->format('j F Y') }}): {{ $post->seoDescription() }}
@empty
- No news published yet.
@endforelse

## Optional

- [Sitemap]({{ route('seo.sitemap') }}): every public page
