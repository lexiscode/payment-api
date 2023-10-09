<?php

use App\Controllers\CustomerController;
use App\Controllers\MethodController;
use App\Controllers\PaymentController;


$app->group('/v1/customers', function ($group) {
    // Define routes using controllers
    $group->get('', [CustomerController::class, 'getAllCustomers']);
    $group->get('/{id}', [CustomerController::class, 'getCustomerById']);
    $group->post('', [CustomerController::class, 'createCustomer']);
    $group->put('/{id:\d+}', [CustomerController::class, 'putCustomer']);
    $group->patch('/{id:\d+}', [CustomerController::class, 'patchCustomer']);
    $group->delete('/{id}', [CustomerController::class, 'deleteCustomer']);

    // New route for activation/deactivation
    $group->put('/activate/{status}', [CustomerController::class, 'activateCustomers']);
});


$app->group('/v1/methods', function ($group) {
    // Define routes using controllers
    $group->get('', [MethodController::class, 'getAllMethods']);
    $group->get('/{id}', [MethodController::class, 'getMethodById']);
    $group->post('', [MethodController::class, 'createMethod']);
    $group->put('/{id:\d+}', [MethodController::class, 'putMethod']);
    $group->patch('/{id:\d+}', [MethodController::class, 'patchMethod']);
    $group->delete('/{id}', [MethodController::class, 'deleteMethod']);

    // New route for activation/deactivation
    $group->put('/activate/{status}', [MethodController::class, 'activateMethods']);
});


$app->group('/v1/payments', function ($group) {
    // Define routes using controllers
    $group->get('', [PaymentController::class, 'getAllPayments']);
    $group->get('/{id}', [PaymentController::class, 'getPaymentById']);
    $group->post('', [PaymentController::class, 'createPayment']);
    $group->put('/{id:\d+}', [PaymentController::class, 'putPayment']);
    $group->patch('/{id:\d+}', [PaymentController::class, 'patchPayment']);
    $group->delete('/{id}', [PaymentController::class, 'deletePayment']);
});

