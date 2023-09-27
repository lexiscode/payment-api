<?php

use App\Controllers\ReferenceController;

// Define routes using the ReferenceController
$app->get('/v2/category/{string}', [ReferenceController::class, 'getCategoriesByName']);
$app->get('/v3/category/{id}', [ReferenceController::class, 'getCategoryById']); 