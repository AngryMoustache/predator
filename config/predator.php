<?php

return [
    'base_uri' => env('PREDATOR_BASE_URI', 'https://predator.angry-moustache.com/api/v1'),
    'auth' => [
        'email' => env('PREDATOR_EMAIL'),
        'password' => env('PREDATOR_PASSWORD'),
    ],
];
