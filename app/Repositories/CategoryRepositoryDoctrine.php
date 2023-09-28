<?php

namespace App\Repositories;

use App\Model\Category;
use Doctrine\ORM\EntityManager;
use App\Repositories\CategoryRepository;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;


class CategoryRepositoryDoctrine implements CategoryRepository
{
    private $entityManager;
    
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function store(Category $category): void
    {
        $this->entityManager->persist($category);
        $this->entityManager->flush();  
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function remove(Category $category): void
    {
        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function update(Category $category): void
    {
        $this->entityManager->persist($category);
        $this->entityManager->flush();
    }

    /**
     * @throws NotSupported
     */
    public function findAll(): array
    {
        return $this->entityManager->getRepository(Category::class)->findAll();
    }

    /**
     * @throws NotSupported
     */
    public function findById(int $id): Category|null
    {
        
        return $this->entityManager->getRepository(Category::class)->find($id);
       
    }

}

