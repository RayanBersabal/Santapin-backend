<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:5173',
        'https://santapin-bwfsbxgvdccybtey.southeastasia-01.azurewebsites.net',
        'https://green-pebble-0905ea900.2.azurestaticapps.net/',
        'https://santapin.store',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
