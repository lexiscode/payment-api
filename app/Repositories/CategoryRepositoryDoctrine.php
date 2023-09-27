<?php

namespace App\Repositories;

use App\Entities\Category;
use Doctrine\ORM\EntityManager;
use App\Repositories\CategoryRepository;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;


class CategoryRepositoryDoctrine implements CategoryRepository
{
    private $entityManager;
    
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function store(Category $category): void
    {
        try {
            $this->entityManager->persist($category);
            $this->entityManager->flush();
        } catch (ORMException $e) {
            error_log($e->getMessage());
        }
    }

    public function remove(Category $category): void
    {
        try {
            $this->entityManager->remove($category);
            $this->entityManager->flush();
        } catch (ORMException $e) {
            error_log($e->getMessage());
        }
    }

    public function update(Category $category): void
    {
        try {
            $this->entityManager->persist($category);
            $this->entityManager->flush();
        } catch (ORMException $e) {
            error_log($e->getMessage());
        }
    }

    public function findAll(): array
    {
        try {
            $categories = $this->entityManager->getRepository(Category::class)->findAll();
        } catch (NotSupported $e) {
            error_log($e->getMessage());
            $categories = [];
        }

        return $categories;
    }

    public function findById(int $id): Category|null
    {
        try {
            return $this->entityManager->getRepository(Category::class)->find($id);
        } catch (\Throwable $e) {
            // Handle any exceptions or errors here
            error_log($e->getMessage());
            return null; // Return null if an error occurs
        }
    }

}

