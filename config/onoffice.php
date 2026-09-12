<?php

declare(strict_types=1);

// config for Innobrain/OnOfficeAdapter
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
     * Retry
     */
    'retry' => [
        'count' => 3,
        'delay' => 200,
        'only_on_connection_error' => true,
    ],

    /**
     * Reuse the HTTP connection to the onOffice API across requests made by
     * the same PHP process. Every call then skips the TCP and TLS handshake
     * (about 130ms) after the first one. Disable this only if your process
     * forks after it has already talked to the API.
     */
    'reuse_connection' => true,

    /**
     * The token and secret to be used for authentication with the OnOffice API.
     */
    'token' => env('ON_OFFICE_TOKEN', ''),
    'secret' => env('ON_OFFICE_SECRET', ''),
];
