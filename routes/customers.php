<?php

use App\Controllers\CustomerController;

// Group routes under '/v1/category' prefix
$app->group('/v1/customers', function ($group) {
    // Define routes using controllers
    $group->get('', [CustomerController::class, 'getAllCustomers']);
    $group->get('/{id}', [CustomerController::class, 'getCustomerById']);
    $group->post('', [CustomerController::class, 'createCustomer']);
    $group->put('/{id:\d+}', [CustomerController::class, 'putCustomer']);
    $group->patch('/{id:\d+}', [CustomerController::class, 'patchCustomer']);
    $group->delete('/{id}', [CustomerController::class, 'deleteCustomer']);
});
