<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <!-- Google reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <!-- Vite compiled CSS & JS -->
    @if (file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json')) || env('VITE_DEV_SERVER'))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>#frontend-warning{position:fixed;z-index:9999;left:12px;bottom:12px;background:#fff8c6;border:1px solid #ffd34d;padding:12px 16px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,.08);font-family:system-ui,-apple-system,Segoe UI,Roboto,'Helvetica Neue',Arial,sans-serif;color:#333}</style>
        <div id="frontend-warning">Frontend assets aren't loaded. Run <code>npm install</code> and then either <code>npm run dev -- --host</code> or <code>npm run build</code>.</div>
    @endif

    <!-- Inertia Head -->
    @inertiaHead

    <!-- Ziggy Routes -->
    @routes
</head>
<body class="font-sans antialiased">
    <!-- Inertia root element -->
    @inertia
</body>
</html>
