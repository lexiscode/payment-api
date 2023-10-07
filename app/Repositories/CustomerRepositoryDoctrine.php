<?php

namespace App\Repositories;

use App\Model\Customer;
use Doctrine\ORM\EntityManager;
use App\Repositories\CustomerRepository;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;


class CustomerRepositoryDoctrine implements CustomerRepository
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
    public function store(Customer $customer): void
    {
        $this->entityManager->persist($customer);
        $this->entityManager->flush();  
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function remove(Customer $customer): void
    {
        $this->entityManager->remove($customer);
        $this->entityManager->flush();
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function update(Customer $customer): void
    {
        $this->entityManager->persist($customer);
        $this->entityManager->flush();
    }

    /**
     * @throws NotSupported
     */
    public function findAll(): array
    {
        return $this->entityManager->getRepository(Customer::class)->findAll();
    }

    /**
     * @throws NotSupported
     */
    public function findById(int $id): Customer|null
    {
        
        return $this->entityManager->getRepository(Customer::class)->find($id);
       
    }

}

