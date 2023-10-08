<?php
namespace App;

use DI\Container;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Mockery;
use Monolog\Logger;
use App\Controllers\AuthController;
use App\Repositories\AuthRepositoryDoctrine;
use PHPUnit\Framework\TestCase;


class AuthControllerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        $container = new Container();
       
        $container->set(EntityManager::class, function (Container $c) {
            $entityManager = Mockery::mock('Doctrine\ORM\EntityManager');
            
            // Set an expectation for the getClassMetadata method
            $entityManager->shouldReceive('getClassMetadata')->andReturn(null);
        
            return $entityManager;
        });
         

        $container->set(EntityRepository::class, function(Container $c) {
            $em = $c->get(EntityManager::class);
            return new AuthRepositoryDoctrine($em);
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
