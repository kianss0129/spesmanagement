<?php

return [

    'credentials_path' => storage_path('app/google/google-credentials.json'),

    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),

    'redirect_uri' => env('GOOGLE_REDIRECT_URI', 'http://localhost/google/callback'),

    'calendar_id' => env('GOOGLE_CALENDAR_ID', 'primary'),

];
