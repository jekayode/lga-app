@php
    $seo = app(\App\Support\Seo::class);
    $documentTitle = $seo->documentTitle($title ?? null);
    $metaDescription = $seo->resolvedDescription();
    $canonicalUrl = $seo->resolvedCanonical();
    $shareImage = $seo->resolvedImage();
    $schemas = $seo->schemas();
@endphp
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $documentTitle }}</title>
<meta name="description" content="{{ $metaDescription }}">
<meta name="robots" content="{{ $seo->resolvedRobots($noindex ?? false) }}">
<link rel="canonical" href="{{ $canonicalUrl }}">

<meta property="og:site_name" content="{{ config('app.name') }}">
<meta property="og:locale" content="{{ config('seo.locale') }}">
<meta property="og:type" content="{{ $seo->resolvedType() }}">
<meta property="og:title" content="{{ $documentTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:image" content="{{ $shareImage }}">
<meta property="og:image:alt" content="{{ $seo->resolvedImageAlt() }}">
@foreach ($seo->openGraphExtras() as $property => $content)
    <meta property="{{ $property }}" content="{{ $content }}">
@endforeach

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $documentTitle }}">
<meta name="twitter:description" content="{{ $metaDescription }}">
<meta name="twitter:image" content="{{ $shareImage }}">
@if (filled(config('seo.twitter_handle')))
    <meta name="twitter:site" content="{{ config('seo.twitter_handle') }}">
@endif

@if ($schemas !== [])
    <script type="application/ld+json">{!! $seo->jsonLd() !!}</script>
@endif

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="manifest" href="/site.webmanifest">
<meta name="theme-color" content="#0B3D5C">
<link rel="alternate" type="text/plain" title="LLM summary" href="{{ url('llms.txt') }}">

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
