<?php

use Tuupola\Middleware\JwtAuthentication;
use Laminas\Diactoros\Response\JsonResponse;

// Define JWT configuration settings
$settings = [
    'jwt' => [
        'secret' => 'SpslTAT3s09W9LjOgt9LQ7VTpSYsZoGD5Zcg0oK3x5U=', 
        'attribute' => 'jwt', 
        'algorithm' => ['HS256'], // The JWT algorithm to use (e.g., HS256)
        'secure' => false, // Only for localhost, but set to true if your application is served over HTTPS i.e. in production
        'error' => function ($response, $arguments) {
            $responseData = [
                'error' => 'Unauthorized',
                'message' => 'Authentication failed'
            ];
            return new JsonResponse($responseData, 401);
        },
    ],
];

// JWT Authentication Middleware
$app->add(new JwtAuthentication([
    "path" => ["/"],  // Exclude this middleware from the root URL
    "ignore" => ["/register", "/login"],  // Exclude these routes from JWT authentication
    "secret" => $settings['jwt']['secret'],
    "attribute" => $settings['jwt']['attribute'],
    "algorithm" => $settings['jwt']['algorithm'],
    "secure" => $settings['jwt']['secure'],
    "error" => $settings['jwt']['error']
]));

