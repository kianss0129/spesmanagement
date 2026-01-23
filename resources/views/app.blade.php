  <!DOCTYPE html>
  <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title inertia>{{ config('app.name', 'Laravel') }}</title>

  <!-- ✅ ADD THIS LINE -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

  @if (file_exists(public_path('build/manifest.json')) || env('VITE_DEV_SERVER') || app()->isLocal())
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @endif


  @inertiaHead
  @routes
  </head>
  <body class="font-sans antialiased">
  @inertia
  </body>
  </html>