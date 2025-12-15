<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    {{-- Vite: include main JS/CSS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Inertia head for page-specific head tags --}}
    @inertiaHead

    {{-- Ziggy routes --}}
    @routes
</head>
<body class="font-sans antialiased">
    @inertia
</body>
</html>
