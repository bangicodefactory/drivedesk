@php($seo = \App\Support\Seo::forRequest(request()))
@php($jsonLd = \App\Support\Seo::jsonLd(request()))
<!DOCTYPE html>
<html lang="{{ $seo['htmlLang'] }}" dir="{{ $seo['dir'] }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SSR is off by design (perf-audit F-23), so React sets the title only
         after it mounts. Social scrapers and most AI crawlers never run JS —
         without these server-rendered tags they saw an empty document. React
         still overrides the title once it boots; this is the crawler's copy. --}}
    <title inertia>{{ $seo['title'] }}</title>
    @if($seo['description'])
        <meta name="description" content="{{ $seo['description'] }}">
    @endif
    <link rel="canonical" href="{{ $seo['canonical'] }}">

    {{-- hreflang needs a distinct URL per language. Locale used to live only in
         the session, so every language shared one URL and a crawler — which has
         no session — only ever saw the guest default. Public pages are now also
         served under /fr, /en, /ar; "/" remains the x-default. --}}
    @foreach($seo['alternates'] as $alternate)
        <link rel="alternate" hreflang="{{ $alternate['hreflang'] }}" href="{{ $alternate['href'] }}">
    @endforeach

    {{-- Only genuine marketing pages are indexable; the admin app, auth screens
         and the installer are noindex even though they are merely unlinked, so a
         leaked URL cannot be indexed. --}}
    @unless($seo['indexable'])
        <meta name="robots" content="noindex, nofollow">
    @endunless

    {{-- Read by the Inertia title callback in app.jsx so the suffix is the
         client's own name. It used to fall back to the template name and every
         public page rendered "… - RentCar". --}}
    <meta name="app-name" content="{{ $seo['siteName'] }}">

    @if($seo['indexable'])
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="{{ $seo['siteName'] }}">
        <meta property="og:title" content="{{ $seo['title'] }}">
        <meta property="og:url" content="{{ $seo['canonical'] }}">
        @if($seo['description'])
            <meta property="og:description" content="{{ $seo['description'] }}">
        @endif
        @if($seo['image'])
            <meta property="og:image" content="{{ $seo['image'] }}">
        @endif
        <meta name="twitter:card" content="{{ $seo['image'] ? 'summary_large_image' : 'summary' }}">
        <meta name="twitter:title" content="{{ $seo['title'] }}">
        @if($seo['description'])
            <meta name="twitter:description" content="{{ $seo['description'] }}">
        @endif
        @if($seo['image'])
            <meta name="twitter:image" content="{{ $seo['image'] }}">
        @endif
        @if($seo['twitterSite'])
            <meta name="twitter:site" content="{{ $seo['twitterSite'] }}">
        @endif
    @endif

    @if($jsonLd)
        <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endif

    {{-- Brand typeface (Nunito) is self-hosted via @fontsource-variable/nunito,
         imported in resources/js/app.jsx and bundled by Vite — no external CDN
         request. Family name 'Nunito Variable' is first in font-sans. --}}

    {{-- Public pages get the trimmed `public` route group (config/ziggy.php);
         the app itself still gets everything. The full list is ~22KB inlined
         into every response, which a first-time marketing visitor should not
         pay for. --}}
    @if($seo['indexable'])
        @routes('public')
    @else
        @routes
    @endif
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    @inertiaHead
</head>
<body class="font-sans antialiased">
    @inertia
</body>
</html>
