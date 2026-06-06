<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title inertia>{{ config('app.name', 'RentCar') }}</title>

    {{-- Brand typeface (Nunito) is self-hosted via @fontsource-variable/nunito,
         imported in resources/js/app.jsx and bundled by Vite — no external CDN
         request. Family name 'Nunito Variable' is first in font-sans. --}}

    @routes
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    @inertiaHead
</head>
<body class="font-sans antialiased">
    @inertia
</body>
</html>
