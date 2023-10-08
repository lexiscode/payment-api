<?php

use App\Controllers\AuthController;


// Login and generate a JWT Token
$app->post('/login', [AuthController::class, 'authLogin']);

// Create an account in order to generate a JWT Token
$app->post('/register', [AuthController::class, 'authRegister']);
