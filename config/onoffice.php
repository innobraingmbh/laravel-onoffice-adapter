<?php

// config for Katalam/OnOfficeAdapter
return [
    /**
     * The base URL of the OnOffice API.
     * Change that if you are using a different version of the API.
     */
    'base_url' => 'https://api.onoffice.de/api/stable/api.php',

    /**
     * The headers to be sent with the request.
     * Override this if you need to send additional headers.
     */
    'headers' => [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ],

    /**
     * The token and secret to be used for authentication with the OnOffice API.
     */
    'token' => env('ON_OFFICE_TOKEN', ''),
    'secret' => env('ON_OFFICE_SECRET', ''),
];
