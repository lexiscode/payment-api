<?php

use App\Controllers\CategoryController;

// Group routes under '/v1/category' prefix
$app->group('/v1/category', function ($group) {
    // Define routes using controllers
    $group->get('', [CategoryController::class, 'getAllCategories']);
    $group->get('/{id}', [CategoryController::class, 'getCategoryById']);
    $group->post('', [CategoryController::class, 'createCategory']);
    $group->put('/{id:\d+}', [CategoryController::class, 'putCategory']);
    $group->patch('/{id:\d+}', [CategoryController::class, 'patchCategory']);
    $group->delete('/{id}', [CategoryController::class, 'deleteCategory']);
});

/*
$app->get('/v1/category', function (Request $request, Response $response) use ($container) {
    $categoryRepository = $container->get(CategoryRepository::class);
    $categories = $categoryRepository->findAll();
    ....
*/