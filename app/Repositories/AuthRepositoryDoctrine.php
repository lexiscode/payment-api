<?php

namespace App\Repositories;

use App\Model\Auth;
use App\Exception\DBException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\ORMException;

class AuthRepositoryDoctrine extends EntityRepository
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct($entityManager, $entityManager->getClassMetadata(Auth::class));
    }


    public function authLogin(string $email, string $password): ?Auth
    {
        try {
            $user = $this->findOneBy(['email' => $email]);

            if ($user && password_verify($password, $user->getPassword())) {
                return $user;
            }

            return null;
        } catch (ORMException $e) {
            throw new DBException('Database Error: ' . $e->getMessage(), 500);
        }
    }

    
    public function emailExists(string $email): bool
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.email = :email')
            ->setParameter('email', $email);

        $count = $queryBuilder->getQuery()->getSingleScalarResult();

        return $count > 0;
    }
    
    public function authRegister(string $email, string $password): bool
    {
        try {
            
            // Check if the email is already registered
            if ($this->emailExists($email)) {
                return false; // Email already exists
            }

            // Create a new Auth entity with the provided email and hashed password
            $user = new Auth();
            $user->setEmail($email);
            $user->setPassword(password_hash($password, PASSWORD_DEFAULT));
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            // Return true if registration is successful
            return true;
        } catch (ORMException $e) {
            throw new DBException('Database Error: ' . $e->getMessage(), 500);
        }
    }

    

}
