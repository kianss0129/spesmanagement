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
    @if (file_exists(public_path('build/manifest.json')) || env('VITE_DEV_SERVER') || app()->isLocal())
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <!-- Inertia Head -->
    @inertiaHead

    <!-- Ziggy Routes -->
    @routes
</head>
<body class="font-sans antialiased">
    <!-- Inertia root element -->
    @inertia

    <!-- Initialize Ziggy in JS -->
    <script>
        if (typeof window !== 'undefined' && typeof window.Ziggy !== 'undefined') {
            window.route = (name, params = {}, absolute = true) => {
                return Ziggy && Ziggy.routes && route(name, params, absolute);
            };
        }
    </script>
</body>
</html>
