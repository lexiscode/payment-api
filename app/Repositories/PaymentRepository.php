<?php

namespace App\Repositories;

use App\Model\Payment;

interface PaymentRepository
{
    // This is where we declare our CRUD Model/Entity methods
    public function findAll(): array;
    public function findById(int $id): Payment|null;
    public function store(Payment $payment): void;
    public function update(Payment $payment): void;
    public function remove(Payment $payment): void;
}


//NB: DO NOT forget to create a container for CustomerRepository, inside config/container.php
