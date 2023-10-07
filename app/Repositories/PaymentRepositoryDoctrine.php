<?php

namespace App\Repositories;

use App\Model\Payment;
use Doctrine\ORM\EntityManager;
use App\Repositories\PaymentRepository;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;


class PaymentRepositoryDoctrine implements PaymentRepository
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
    public function store(Payment $payment): void
    {
        $this->entityManager->persist($payment);
        $this->entityManager->flush();  
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function remove(Payment $payment): void
    {
        $this->entityManager->remove($payment);
        $this->entityManager->flush();
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function update(Payment $payment): void
    {
        $this->entityManager->persist($payment);
        $this->entityManager->flush();
    }

    /**
     * @throws NotSupported
     */
    public function findAll(): array
    {
        return $this->entityManager->getRepository(Payment::class)->findAll();
    }

    /**
     * @throws NotSupported
     */
    public function findById(int $id): Payment|null
    {
        
        return $this->entityManager->getRepository(Payment::class)->find($id);
       
    }

}