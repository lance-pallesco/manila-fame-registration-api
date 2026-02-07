<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    /*
     * Paths that should have CORS headers applied.
     * The API routes need CORS to accept requests from the Vue SPA frontend.
     */
    'paths' => ['api/*', 'storage/*'],

    /*
     * Allowed HTTP methods.
     */
    'allowed_methods' => ['*'],

    /*
     * Allowed origins for CORS requests.
     * Configure this based on your frontend URL.
     * Use env variable for flexibility across environments.
     */
    'allowed_origins' => [
        env('FRONTEND_URL', 'http://localhost:5173'),
        'http://localhost:3000',
        'http://localhost:8080',
        'http://127.0.0.1:5173',
    ],

    /*
     * Patterns for allowed origins (regex).
     */
    'allowed_origins_patterns' => [],

    /*
     * Allowed request headers.
     */
    'allowed_headers' => ['*'],

    /*
     * Headers that can be exposed to the browser.
     */
    'exposed_headers' => [],

    /*
     * Max age for preflight request caching (in seconds).
     */
    'max_age' => 0,

    /*
     * Whether to include credentials (cookies, authorization headers).
     */
    'supports_credentials' => false,

];
