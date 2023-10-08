<?php

namespace App\Repositories;

use App\Model\Method;

interface MethodRepository
{
    // This is where we declare our CRUD Model/Entity methods
    public function findAll(): array;
    public function findById(int $id): Method|null;
    public function store(Method $method): void;
    public function update(Method $method): void;
    public function remove(Method $method): void;
}
