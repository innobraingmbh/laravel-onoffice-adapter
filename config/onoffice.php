<?php

// config for Katalam/OnOfficeAdapter
return [
    'base_url' => 'https://api.onoffice.de/api/stable/api.php',
    'headers' => [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ],

    'token' => env('ON_OFFICE_TOKEN', ''),
    'secret' => env('ON_OFFICE_SECRET', ''),
];
