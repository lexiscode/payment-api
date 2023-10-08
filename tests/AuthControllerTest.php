<?php
namespace App;

use DI\Container;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Mockery;
use Monolog\Logger;
use App\Controllers\AuthController;
use App\Repositories\AuthRepositoryDoctrine;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManagerInterface;

use PHPUnit\Framework\TestCase;


class AuthControllerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        // Create a PHPUnit mock for EntityManager and ClassMetadata
        $entityManager = $this->getMockBuilder(EntityManagerInterface::class)
            ->getMock();
        
        $classMetadata = $this->getMockBuilder(ClassMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Set up EntityManager to return the ClassMetadata mock
        $entityManager->method('getClassMetadata')->willReturn($classMetadata);

        // Create and set up the container
        $container = new Container();
        $container->set(EntityManagerInterface::class, $entityManager);
        $container->set(EntityRepository::class, function(Container $c) use ($entityManager) {
            return new AuthRepositoryDoctrine($entityManager);
        });
        $container->set(Logger::class, function(Container $c) {
            return Mockery::mock('Monolog\Logger');
        });

        $this->container = $container;
    }

    public function testCreateInstanceOfAuthController()
    {
        $abstractControllerObject = new AuthController($this->container);
        $this->assertInstanceOf('App\Controllers\AuthController', $abstractControllerObject);
    }
}
