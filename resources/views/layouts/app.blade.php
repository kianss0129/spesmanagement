<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    {{-- Scripts --}}
    @routes
    {{-- Include Vite when built assets exist, a dev server is enabled, or running locally --}}
    @if (file_exists(public_path('build/manifest.json')) || env('VITE_DEV_SERVER') || app()->isLocal())
        @if (isset($page['component']) && $page['component'])
            @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @else
            @vite(['resources/js/app.js'])
        @endif
    @endif

    {{-- Development warning banner is removed by the frontend bundle when it loads --}}
    <style>#frontend-warning{position:fixed;z-index:9999;left:12px;bottom:12px;background:#fff8c6;border:1px solid #ffd34d;padding:12px 16px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,.08);font-family:system-ui,-apple-system,Segoe UI,Roboto,'Helvetica Neue',Arial,sans-serif;color:#333}</style>
    <div id="frontend-warning">Frontend assets aren't loaded. If developing, run <code>npm run dev -- --host</code> or build assets with <code>npm run build</code>.</div>

    @if (isset($page))
        @inertiaHead
    @endif
</head>
<body class="font-sans antialiased">
    @if (isset($page))
        @inertia   {{--  Inertia mounts the Vue app here --}}
    @else
        @yield('content')
    @endif
</body>
</html>
