<?php

namespace App\Repositories;

use App\Model\Customer;

interface CustomerRepository
{
    // This is where we declare our CRUD Model/Entity methods
    public function findAll(): array;
    public function findById(int $id): Customer|null;
    public function store(Customer $customer): void;
    public function update(Customer $customer): void;
    public function remove(Customer $customer): void;
}


//NB: DO NOT forget to create a container for CustomerRepository, inside config/container.php