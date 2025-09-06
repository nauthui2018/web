<?php

/**
 * CORS configuration file.
 * This file defines the CORS settings for the application.
 * It allows cross-origin requests from any origin with any method and headers.
 */
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];