<?php

use App\Controllers\CategoryController;

// Define routes using controllers
$app->get('/v1/category', [CategoryController::class, 'getAllCategories']);
$app->get('/v1/category/{id}', [CategoryController::class, 'getCategoryById']);
$app->post('/v1/category', [CategoryController::class, 'createCategory']);
$app->put('/v1/category/{id:\d+}', [CategoryController::class, 'putCategory']);
$app->patch('/v1/category/{id:\d+}', [CategoryController::class, 'patchCategory']); 
$app->delete('/v1/category/{id}', [CategoryController::class, 'deleteCategory']);


/*
$app->get('/v1/category', function (Request $request, Response $response) use ($container) {
    $categoryRepository = $container->get(CategoryRepository::class);
    $categories = $categoryRepository->findAll();
    ....
*/