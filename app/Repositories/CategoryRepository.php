<?php

namespace App\Repositories;

use App\Model\Category;

interface CategoryRepository
{
    // This is where we declare our CRUD Model/Entity methods
    public function findAll(): array;
    public function findById(int $id): Category|null;
    public function store(Category $category): void;
    public function update(Category $category): void;
    public function remove(Category $category): void;
}


//NB: DO NOT forget to create a container for CategoryRepository, inside config/container.php