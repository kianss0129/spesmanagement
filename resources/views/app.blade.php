<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    {{-- Vite: include main JS/CSS --}}
    @if (file_exists(public_path('build/manifest.json')) || env('VITE_DEV_SERVER') || app()->isLocal())
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>#frontend-warning{position:fixed;z-index:9999;left:12px;bottom:12px;background:#fff8c6;border:1px solid #ffd34d;padding:12px 16px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,.08);font-family:system-ui,-apple-system,Segoe UI,Roboto,'Helvetica Neue',Arial,sans-serif;color:#333}</style>
    <div id="frontend-warning">Frontend assets aren't loaded. If developing, run <code>npm run dev -- --host</code> or build assets with <code>npm run build</code>.</div>

    {{-- Inertia head for page-specific head tags --}}
    @inertiaHead

    {{-- Ziggy routes --}}
    @routes
</head>
<body class="font-sans antialiased">
    @inertia
</body>
</html>
