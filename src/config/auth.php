<?php

return [
    'secret' => env('JWT_SECRET', 'devfinder-secret-key-2026'),
    'expires_in' => env('JWT_EXPIRES_IN', 604800), // 7 days in seconds
    'algorithm' => 'HS256',
];
