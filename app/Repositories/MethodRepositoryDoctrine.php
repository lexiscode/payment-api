<?php

namespace App\Repositories;

use App\Model\Method;
use Doctrine\ORM\EntityManager;
use App\Repositories\CustomerRepository;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;


class MethodRepositoryDoctrine implements MethodRepository
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
    public function store(Method $method): void
    {
        $this->entityManager->persist($method);
        $this->entityManager->flush();  
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function remove(Method $method): void
    {
        $this->entityManager->remove($method);
        $this->entityManager->flush();
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function update(Method $method): void
    {
        $this->entityManager->persist($method);
        $this->entityManager->flush();
    }

    /**
     * @throws NotSupported
     */
    public function findAll(): array
    {
        return $this->entityManager->getRepository(Method::class)->findAll();
    }

    /**
     * @throws NotSupported
     */
    public function findById(int $id): Method|null
    {
        
        return $this->entityManager->getRepository(Method::class)->find($id);
       
    }
}
